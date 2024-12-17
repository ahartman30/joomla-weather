<?php

namespace Weather\Plugin\Content\OpenData\Extension;

use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Filesystem\Path;

defined('_JEXEC') or die('Restricted access');

/**
 * Responsible for fetching products from opendata.
 *
 * @package     Weather\Plugin\Content\OpenData\Extension
 *
 * @since       1.0.0
 */
class DataLoader {

  const CACHE_DIR = "media/weatheropendata/cache";

  private string $ftp_user;
  private string $ftp_passwd;
  private string $ftp_host;
  private bool $ftp_passive;
  private string $cache_dir;
  private mixed $ftp;

  function __construct() {
    $params            = ComponentHelper::getParams('com_weatheropendata');
    $this->ftp_user    = trim($params->get('opendata_username'));
    $this->ftp_passwd  = trim($params->get('opendata_password'));
    $this->ftp_host    = $params->get('opendata_host');
    $this->ftp_passive = $params->get('opendata_passive');
    $this->cache_dir   = $this->resolveCleanAbsolutePath(self::CACHE_DIR);
  }

  /**
   * Connects to the opendata ftp server.
   *
   * @throws Exception If connection fails.
   * @since 1.0.0
   */
  public function connect(): void {
    $this->ftp = ftp_connect($this->ftp_host, 21, 15);
    if ($this->ftp === false) {
      throw new Exception("Kein FTP login zum Host '" . $this->ftp_host . "' möglich. Bitte Nutzer und Kennwort prüfen.");
    }
    $login = ftp_login($this->ftp, $this->ftp_user, $this->ftp_passwd);
    if (!$login) {
      throw new Exception("Kein FTP login zum Host '" . $this->ftp_host . "' möglich. Bitte Nutzer und Kennwort prüfen.");
    }
    ftp_pasv($this->ftp, $this->ftp_passive);
  }

  /**
   * Disconnects from the opendata ftp server and releases resources.
   *
   * @since 1.0.0
   */
  public function disconnect(): void {
    ftp_close($this->ftp);
  }

  private function resolveCleanAbsolutePath($relativePath): string {
    $path = JPATH_BASE . '/' . $relativePath . '/';
    $path = Path::clean($path);

    return $path;
  }

  /**
   * Loads the given product from opendata into the local cache and returns its data.
   *
   * @param   string  $product  The product to load.
   *
   * @return array|null Array with product name, cache file path and url the product was fetched from. Null in
   *                    case of fetchting prodcut from web failed.
   *
   * @throws Exception If the given product doesn't exist.
   * @since 1.0.0
   */
  public function loadProduct(string $product): ?array {
    $data = $this->loadData($product);
    if (!is_array($data)) {
      throw new Exception($product . ": Das Produkt existiert nicht.");
    }
    if ($data['protocol'] == "http" || $data['protocol'] == "https") {
      return $this->getProductFromWeb($product, $data);
    }
    else {
      return $this->getProductFromOpendata($product, $data);
    }
  }

  /**
   * Gets a cached file for the given product.
   *
   * @param   string  $product  The product to load.
   *
   * @return array|null Array with product type and cache file path, null if no cache file exists.
   *
   * @throws Exception If the given product doesn't exist.
   * @since 1.0.0
   */
  public function loadFromCache(string $product): ?array {
    $data = $this->loadData($product);
    if (!is_array($data)) {
      throw new Exception($product . ": Das Produkt existiert nicht.");
    }
    $type = $data['product'];
    $file = $this->getCacheFileIfExisting($product, $type);
    if ($file == null) return null;

    return array($type, $file);
  }


  /**
   * Queries the data for the given product from the database.
   *
   * @param   string  $product  The product name to load its data.
   *
   * @return mixed|null Associative array with result or null if not found.
   * @throws Exception On error loading product data from the db.
   * @since 1.0.0
   */
  private function loadData(string $product) {
    $db    = Factory::getDbo();
    $query = $db->getQuery(true);
    $query->select('name, protocol, file, product, cache_minutes');
    $query->from('#__weatheropendata_products');
    $query->where("name=" . $query->quote($product, true));
    $db->setQuery($query);
    $result = null;
    try {
      $result = $db->loadAssoc();
    }
    catch (Exception $e) {
      throw new Exception($product . ": Fehler beim laden der Produktdaten. " . $e->getMessage());
    }

    return $result;
  }

  /**
   * Fetches a product from opendata.
   *
   * @param   string  $product  The product to fetch.
   * @param   array   $data     The product data.
   *
   * @return array Product type, path to cached files and source url.
   *
   * @throws Exception If the product is not available at opendata or the cached file is not avalailable.
   * @since 1.0.0
   */
  private function getProductFromOpendata(string $product, array $data): array {
    $productType = $data["product"];
    $cacheFile   = $this->getCacheFileIfExisting($product, $productType);
    $url         = null;
    if ($this->cacheExpired($product, $data)) {
      $fileTime = $cacheFile ?? filemtime($cacheFile);

      # Fetch file list for product
      $path = '/' . $this->resolvePlaceHolders($data["file"]);
      $dir  = pathinfo($path, PATHINFO_DIRNAME);
      $file = pathinfo($path, PATHINFO_BASENAME);
      ftp_chdir($this->ftp, $dir);
      $fileList = ftp_nlist($this->ftp, $file);
      if ($fileList === false || count($fileList) == 0) {
        throw new Exception($product . ": Produkt nicht im Opendata vorhanden oder nicht abrufbar.");
      }

      # Fetch product
      $fileNewest = $this->getNewestProduct($fileList);
      $timestamp  = ftp_mdtm($this->ftp, $fileNewest);
      if ($timestamp > $fileTime) { // if ftp file is newer than cache
        $ext       = $this->isImage($productType) ? pathinfo($fileNewest, PATHINFO_EXTENSION) : "txt";
        $cacheFile = $this->getCacheFile($product, $ext);
        ftp_get($this->ftp, $cacheFile, $fileNewest, FTP_BINARY);
        if (!$this->isImage($productType)) {
          $content = file_get_contents($cacheFile);
          $content = $this->formatContent($content, $productType);
          file_put_contents($cacheFile, $content);
        }
        touch($cacheFile, $timestamp, $timestamp);
      }
      $url = $data['protocol'] . '://' . $this->ftp_host . $dir . '/' . $fileNewest;
    }
    if (!file_exists($cacheFile)) throw new Exception($product . ": Lokale Cache-Datei existiert nicht.");

    return array($data['product'], $cacheFile, $url);
  }

  private function resolvePlaceHolders($path) {
    if (str_contains($path, "%")) {
      $path = str_replace("%Y", gmdate("Y"), $path);
      $path = str_replace("%m", gmdate("m"), $path);
      $path = str_replace("%d", gmdate("d"), $path);
      $path = str_replace("%m", gmdate("H"), $path);
    }

    return $path;
  }

  /**
   * This function evaluates the given file names for the Opendata product and
   * returns the newest.
   *
   * Some products with WMO file names need a special handling when a new month
   * begins:
   * TTAAii_CCCC_ddHHmm -> consider correct sorting for newest for WMO
   * filenames.
   *
   * @param   array  $fileList  The file names.
   *
   * @return string  The file name for the newest product.
   * @since 1.0.0
   */
  private function getNewestProduct(array $fileList): string {
    $isWmo = preg_match('/^(\w{4}\d{2}_\w{4})_\d{6}/i', $fileList[0]);
    if ($isWmo) $fileList = $this->filterMonthWmo($fileList);
    rsort($fileList);

    return $fileList[0];
  }

  /**
   * Filters actual month of WMO product file names.
   * If day contains 0 then remove 3 and 2.
   *
   * @since 1.0.0
   */
  private function filterMonthWmo(array $files): array {
    $hasDay0 = false;
    foreach ($files as $file) {
      $hasDay0 = $hasDay0 || ($file[12] == "0");
    }
    if (!$hasDay0) return $files;

    $result = array();
    foreach ($files as $file) {
      if ($file[12] == '0' || $file[12] == '1') $result[] = $file;
    }

    return $result;
  }

  private function formatContent(string $content, string $productType): string {
    $className = "Weather\\Plugin\\Content\\OpenData\\Extension\\Formatter\\" . strtoupper($productType);
    if (class_exists($className)) {
      $formatter = new $className;
      $content   = $formatter->format($content);
    }

    return $content;
  }

  /**
   * Fetches a product from a web url.
   *
   * @param   string  $product  The product to fetch.
   * @param   array   $data     The product data.
   *
   * @return array|null Product type, path to cached files and source url or null if the cached file not exists.
   *
   * @throws Exception If fetching the product failed.
   * @since 1.0.0
   */
  private function getProductFromWeb(string $product, array $data): ?array {
    $ext           = pathinfo($data["file"], PATHINFO_EXTENSION);
    $productType   = $data['product'];
    $cacheFile     = $this->getCacheFileIfExisting($product, $productType);
    $cacheFileHash = null;
    if ($cacheFile != null) $cacheFileHash = md5(file_get_contents($cacheFile));

    $url = $data['protocol'] . '://' . $data["file"];
    try {
      $newFileContent = $this->getContentsFromUrl($url);
    }
    catch (Exception $e) {
      throw new Exception($product . ": Produkt konnte nicht aus dem Web geholt werden. " . $e->getMessage());
    }
    $newFileHash = md5($newFileContent);

    if ($newFileHash != $cacheFileHash) {
      $cacheFile = $this->getCacheFile($product, $ext);
      if (!$this->isImage($productType)) {
        $newFileContent = $this->formatContent($newFileContent, "");
      }
      $result = file_put_contents($cacheFile, $newFileContent);
      if ($result === false) {
        throw new Exception($product . ": Fehler beim Speichern.");
      }
    }

    if (!file_exists($cacheFile)) return null;

    return array($data['product'], $cacheFile, $url);
  }

  /**
   * Fetches the content for a given url using curl.
   *
   * @param   string  $url  The url to fetch the content from.
   *
   * @return string The content of the given url.
   * @throws Exception On error fetching the url.
   * @since 1.0.0
   */
  private function getContentsFromUrl(string $url): string {
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_USERAGENT, 'php');
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYSTATUS, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    $content = curl_exec($curl);
    if ($content === false) {
      $error = curl_error($curl);
      curl_close($curl);
      throw new Exception($error);
    }
    curl_close($curl);

    return $content;
  }

  public function isImage(string $productType): bool {
    return $productType == "img";
  }

  private function getCacheFile(string $productName, string $extension): string {
    return $this->cache_dir . "/" . $productName . "." . $extension;
  }

  private function getCacheFileIfExisting($productName, $productType) {
    $file = $this->cache_dir . "/" . $productName . ".";
    if ($this->isImage($productType)) {
      $result = glob($file . "*");
      if ($result !== false && count($result) > 0) {
        $file = $result[0];
      }
    }
    else {
      $file .= "txt";
    }

    return file_exists($file) ? $file : null;
  }

  /**
   * Checks if the cache is expired for the goven product.
   *
   * @param   string  $product  The product to check the cache for.
   *
   * @return bool True if the cache is expired, false otherwise.
   *
   * @throws Exception If the product doesn't exist.
   * @since 1.0.0
   */
  public function isCacheExpired(string $product): bool {
    $data = $this->loadData($product);
    if (!is_array($data)) {
      throw new Exception($product . ": Das Produkt existiert nicht.");
    }

    return $this->cacheExpired($product, $data);
  }

  private function cacheExpired(string $product, array $data): bool {
    $productType = $data["product"];
    $cacheFile   = $this->getCacheFileIfExisting($product, $productType);
    $checkInMin  = $data["cache_minutes"];

    # CACHE-check
    $cacheExpired  = true;
    if (isset($cacheFile) && is_readable($cacheFile)) {
      $fileTimestamp         = filemtime($cacheFile);
      $timestampCacheExpires = $fileTimestamp + ($checkInMin * 60);
      if ($timestampCacheExpires > time()) $cacheExpired = false;
    }

    return $cacheExpired;
  }

}
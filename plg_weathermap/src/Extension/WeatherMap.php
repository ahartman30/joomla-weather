<?php

namespace Weather\Plugin\Content\WeatherMap\Extension;

use Exception;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Filesystem\Path;
use Joomla\String\StringHelper;
use RuntimeException;

defined('_JEXEC') or die();

class WeatherMap extends CMSPlugin {

	const LEAFLET_VERSION = "1.3.1";
	const PLUGIN_LOADING_VERSION = "0.1.24";
	const PLUGIN_POPUP_VERSION = "0.2.0";
	const PLUGIN_TIMEDIMENSION_VERSION = "1.1.0";
	const PLUGIN_FULLSCREEN_VERSION = "1.4.5";
	const WARNING_MAP_WMS_VERSION = "1.0.0";

	private const CMD = "wetterkarte";
	private const MEDIA_DIR = "media/weathermap";

	private const DEFAULT_TEMPLATE = "wettermodul";
	private const DEFAULT_ID = "wetterkarte";
	private const DEFAULT_WIDTH = "450px";
	private const DEFAULT_HEIGHT = "600px";
	private const DEFAULT_LAT = "50.04938";
	private const DEFAULT_LON = "8.79026";
	private const DEFAULT_ZOOM = "8";
	private const DEFAULT_ZOOM_MAX = "12";
	private const DEFAULT_ZOOM_MIN = "5";
	private const DEFAULT_BOUNDS = "[47.1408,4.7043],[55.4381,15.2951]";
	private const DEFAULT_POPUPTEXT = "";
	private const DEFAULT_FULLSCREEN = "false";
	const PATTERN = '/\{' . self::CMD . '\s*(.*?)}/s';

	private string $cmd;
	private string $mediaDir;
	private string $localMediaDir;

	/**
	 * This is the first stage in preparing content for output and is the most common point for content orientated
	 * plugins to do their work. Since the article and related parameters are passed by reference, event handlers can
	 * modify them prior to display.
	 *
	 * @param   string  $context  The context of the content being passed to the plugin.
	 * @param   mixed   $row      An object with a "text" property
	 * @param   mixed   $params   Additional parameters. See {@see PlgContentContent()}.
	 * @param   int     $page     Optional page number. Unused. Defaults to zero.
	 *
	 * @return  void
	 * @since 1.0.0
	 */
	public function onContentPrepare(string $context, mixed $row, mixed $params, int $page = 0): void {

		// simple performance check to determine if to proceed
		if (!$this->getApplication()->isClient('site')) return; // Skip processing in admin interface
		if (!$this->isWeatherMapOn($row->text)) return;

		$this->mediaDir      = self::MEDIA_DIR;
		$this->localMediaDir = $this->resolveCleanAbsolutePath(self::MEDIA_DIR);
		$properties          = $this->parseCmd($row->text);
		$templateFile        = $this->resolveTemplateFile($properties["%template%"]);
		if (!file_exists($templateFile))
		{
			$this->error('Template ' . $templateFile . ' existiert nicht!');

			return;
		}
		$this->loadLeaflet();
		$weatherMapHtml = $this->parseTemplate($templateFile, $properties);
		$row->text      = str_replace($this->cmd, $weatherMapHtml, $row->text);
	}

	private function isWeatherMapOn(string $text): bool {
		return !(StringHelper::strpos($text, '{' . self::CMD) === false);
	}

	private function resolveCleanAbsolutePath(string $relativePath): string {
		$path = JPATH_BASE . '/' . $relativePath;
		$path = Path::clean($path);

		return realpath($path);
	}

	private function resolveTemplateFile(string $templateName): string {
		$templateFile = $this->localMediaDir . "/" . $templateName . '.html';

		return $templateFile;
	}

	private function loadLeaflet(): void {
		$document = Factory::getDocument();
		$document->addScript($this->mediaDir . "/leaflet/leaflet.js", array("version" => self::LEAFLET_VERSION), array());
		$document->addStyleSheet($this->mediaDir . "/leaflet/leaflet.css", array("version" => self::LEAFLET_VERSION), array());

		$document->addScript($this->mediaDir . "/leaflet-warningmap-wms.js", array("version" => self::WARNING_MAP_WMS_VERSION), array());
		$document->addScript($this->mediaDir . "/leaflet/plugins/loading/Control.Loading.js", array("version" => self::PLUGIN_LOADING_VERSION), array());
		$document->addStyleSheet($this->mediaDir . "/leaflet/plugins/loading/Control.Loading.css", array("version" => self::PLUGIN_LOADING_VERSION), array());
		$document->addScript($this->mediaDir . "/leaflet/plugins/responsivepopup/leaflet.responsive.popup.js", array("version" => self::PLUGIN_POPUP_VERSION), array());
		$document->addStyleSheet($this->mediaDir . "/leaflet/plugins/responsivepopup/leaflet.responsive.popup.css", array("version" => self::PLUGIN_POPUP_VERSION), array());
		$document->addScript($this->mediaDir . "/leaflet/plugins/timedimension/leaflet.timedimension.min.js", array("version" => self::PLUGIN_TIMEDIMENSION_VERSION), array());
		$document->addStyleSheet($this->mediaDir . "/leaflet/plugins/timedimension/leaflet.timedimension.control.min.css", array("version" => self::PLUGIN_TIMEDIMENSION_VERSION), array());
		$document->addScript($this->mediaDir . "/leaflet/plugins/timedimension/iso8601.min.js", array("version" => self::PLUGIN_TIMEDIMENSION_VERSION), array());
		$document->addStyleSheet($this->mediaDir . "/leaflet/plugins/fullscreen/Control.FullScreen.css", array("version" => self::PLUGIN_FULLSCREEN_VERSION), array());
		$document->addScript($this->mediaDir . "/leaflet/plugins/fullscreen/Control.FullScreen.js", array("version" => self::PLUGIN_FULLSCREEN_VERSION), array());
	}

	private function parseCmd(string $text): array {
		$defaultProperties = array(
			'%template%'   => self::DEFAULT_TEMPLATE,
			'%id%'         => self::DEFAULT_ID,
			'%width%'      => self::DEFAULT_WIDTH,
			'%height%'     => self::DEFAULT_HEIGHT,
			'%lat%'        => self::DEFAULT_LAT,
			'%lon%'        => self::DEFAULT_LON,
			'%zoom%'       => self::DEFAULT_ZOOM,
			'%zoom_max%'   => self::DEFAULT_ZOOM_MAX,
			'%zoom_min%'   => self::DEFAULT_ZOOM_MIN,
			'%bounds%'     => self::DEFAULT_BOUNDS,
			'%popuptext%'  => self::DEFAULT_POPUPTEXT,
			'%fullscreen%' => self::DEFAULT_FULLSCREEN
		);

		preg_match_all(self::PATTERN, $text, $matches);
		$this->cmd     = $matches[0][0];
		$rawProperties = $matches[1][0];
		$rawProperties = preg_replace('/\s+/', '', $rawProperties);
		$rawProperties = preg_replace('/(\w+)=/', '%$1%=', $rawProperties);
		$rawProperties = strtr($rawProperties, ';', '&');
		parse_str($rawProperties, $properties);

		return array_merge($defaultProperties, $properties);
	}

	private function parseTemplate(string $templateFile, array $properties): string {
		$template = file_get_contents($templateFile);

		return strtr($template, $properties);
	}

	private function error(string $msg): void {
		if ($this->loggedIn())
		{
			$this->getApplication()->enqueueMessage($msg, CMSApplicationInterface::MSG_ERROR);
		}
	}

	private function loggedIn(): bool {
		try
		{
			$user = Factory::getApplication()->getIdentity();
		}
		catch (Exception $e)
		{
			throw new RuntimeException("Getting Application object failed.", $e);
		}
		if ($user->id)
		{
			return true;
		}

		return false;
	}

}

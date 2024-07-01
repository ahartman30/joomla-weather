<?php

namespace Weather\Plugin\Console\OpenData\CliCommand;

defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Factory;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Filesystem\File;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Weather\Plugin\Content\OpenData\Extension\DataLoader;

class RunFetchProductCommand extends AbstractCommand {

  /**
   * The default command name
   *
   * @var    string
   * @since  4.0.0
   */
  protected static $defaultName = 'weatheropendata:fetchproduct';

  /**
   * Internal function to execute the command.
   *
   * @param   InputInterface   $input   The input to inject into the command.
   * @param   OutputInterface  $output  The output to inject into the command.
   *
   * @return  integer  The command exit code
   *
   * @since   4.0.0
   */
  protected function doExecute(InputInterface $input, OutputInterface $output): int {
    $out = new SymfonyStyle($input, $output);
    $productName = (string) $input->getOption('product');
    while (!$productName) {
      $productName = (string) $out->ask('Please enter a product name');
    }

    $out->title('Weather Opendata');

    $dataLoader = new DataLoader();
    try {
      $dataLoader->connect();
    }
    catch (Exception $e) {
      $out->error($e->getMessage());
      return 1;
    }

    /*$db    = Factory::getDbo();
    $query = $db->getQuery(true);
    $query->select('name, protocol, file, cache_minutes');
    $query->from('#__weatheropendata_products');
    $query->where('name = "' . $productName . '"');
    $db->setQuery($query);
    try {
      $product = $db->loadAssoc();
    }
    catch (Exception $e) {
      $out->error($e->getMessage());
      $out->error('Datenabruf fehlgeschlagen.');
      return 1;
    }

    if (!$product){
      $out->error('Produkt "' . $productName . '" existiert nicht.');
      return 1;
    }*/

    // TODO Testen ohne vorher Existenz zu prüfen.

    $out->info(sprintf('Starte Datenabruf für Produkt %s ...', $productName));
    try {
      $productData = $dataLoader->loadFromCache($productName);
      if ($productData) {
        $cacheFile = $productData[1];
        File::delete($cacheFile);
      }
      $productData = $dataLoader->loadProduct($productName);
      $productUrl  = $productData[2];
    }
    catch (Exception $e) {
      $out->error($e->getMessage());
      return 1;
    } finally {
      $dataLoader->disconnect();
    }
    $out->success($productUrl);
    return 0;
  }

  /**
   * Configure the command.
   *
   * @return  void
   *
   * @since   4.0.0
   */
  protected function configure(): void {
    $this->addOption('product', null, InputOption::VALUE_REQUIRED, 'Name of the product to fetch.');
    $this->setDescription('This command fetches a single opendata product into the local cache, ignoring the cache time.');
    $this->setHelp(
      <<<EOF
The <info>%command.name%</info> fetches a single opendata product into the local cache, ignoring the cache time.
<info>php %command.full_name%</info>
EOF
    );
  }

}
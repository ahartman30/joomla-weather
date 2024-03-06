<?php

namespace Weather\Plugin\Console\OpenData\CliCommand;

defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Factory;
use Joomla\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Weather\Plugin\Content\OpenData\Extension\DataLoader;

class RunLoadCacheCommand extends AbstractCommand {

  /**
   * The default command name
   *
   * @var    string
   * @since  4.0.0
   */
  protected static $defaultName = 'weatheropendata:loadcache';

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
    $out->title('Weather Opendata');

    $dataLoader = new DataLoader();
    try {
      $dataLoader->connect();
    }
    catch (Exception $e) {
      $out->error($e->getMessage());

      return 1;
    }

    $db    = Factory::getDbo();
    $query = $db->getQuery(true);
    $query->select('name, protocol, file, cache_minutes');
    $query->from('#__weatheropendata_products');
    $query->order('name ASC');
    $db->setQuery($query);
    $products = null;
    try {
      $products = $db->loadAssocList();
    }
    catch (Exception $e) {
      $out->error($e->getMessage());

      return 1;
    }
    $out->info('Starte Datenabruf von ' . count($products) . ' Produkten...');
    $countProductsFetched = 0;
    $countProductsError   = 0;
    foreach ($products as $product) {
      $productName = $product['name'];
      try {
        if (!$dataLoader->isCacheExpired($productName)) {
          $out->writeln(sprintf('%-45s ->   aktuell (%d Min)', $productName, $product['cache_minutes']));
          continue;
        }
        $productData = $dataLoader->loadProduct($productName);
        $productType = $productData[0];
        $cacheFile   = $productData[1];
        $productUrl  = $productData[2];
        $out->writeln(sprintf('%-45s ->   %s (%d Min)', $productName, $productUrl, $product['cache_minutes']));
      }
      catch (Exception $e) {
        $countProductsError++;
        $out->writeln(sprintf('%-45s ->   %s://%s (%d Min)', $productName, $product['protocol'], $product['file'], $product['cache_minutes']));
        $out->writeln("    ERROR: " . $e->getMessage());
        continue;
      }
      $countProductsFetched++;
    }
    $dataLoader->disconnect();
    $out->success('Datenabruf abgeschlossen, ' . $countProductsFetched . ' Produkte abgerufen, ' . $countProductsError . ' Produkte fehlgeschlagen.');

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
    $this->setDescription('This command loads the complete opendata products into the local cache.');
    $this->setHelp(
      <<<EOF
The <info>%command.name%</info> command loads the complete opendata products into the local cache.
<info>php %command.full_name%</info>
EOF
    );
  }

}
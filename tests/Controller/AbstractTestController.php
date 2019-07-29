<?php

declare(strict_types=1);

namespace Tests\SitemapPlugin\Controller;

use ApiTestCase\XmlApiTestCase;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Currency\Model\Currency;
use Sylius\Component\Currency\Model\CurrencyInterface;
use Sylius\Component\Locale\Model\Locale;
use Sylius\Component\Locale\Model\LocaleInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

abstract class AbstractTestController extends XmlApiTestCase
{
    /** @var ChannelInterface */
    protected $channel;

    /** @var LocaleInterface */
    protected $locale;

    /** @var LocaleInterface */
    protected $locale2;

    /** @var CurrencyInterface */
    protected $currency;

    /**
     * @before
     */
    public function setupDatabase()
    {
        echo 'Setting up database in abstract';

        parent::setUpDatabase();

        $this->locale = new Locale();
        $this->locale->setCode('en_US');

        $this->getEntityManager()->persist($this->locale);

        $this->locale2 = new Locale();
        $this->locale2->setCode('nl_NL');

        $this->getEntityManager()->persist($this->locale2);

        $this->currency = new Currency();
        $this->currency->setCode('USD');

        $this->getEntityManager()->persist($this->currency);

        $this->channel = new Channel();
        $this->channel->setCode('US_WEB');
        $this->channel->setName('US Web Store');
        $this->channel->setDefaultLocale($this->locale);
        $this->channel->setBaseCurrency($this->currency);
        $this->channel->setTaxCalculationStrategy('order_items_based');

        $this->channel->addLocale($this->locale);
        $this->channel->addLocale($this->locale2);

        $this->getEntityManager()->persist($this->channel);
        $this->getEntityManager()->flush();
    }

    public function generateSitemaps(): void
    {
        echo 'Generating sitemaps';

        $application = new Application(\Tests\SitemapPlugin\Application\Kernel::class);
        $application->setAutoExit(false);

        $input = new ArrayInput(array(
            'command' => 'sylius:sitemap:generate',
        ));

        $output = new BufferedOutput();
        $application->run($input, $output);

        echo $output->fetch();
    }
}

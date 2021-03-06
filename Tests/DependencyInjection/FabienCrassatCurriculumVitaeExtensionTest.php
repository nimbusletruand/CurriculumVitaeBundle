<?php

/*
 * This file is part of the FabienCrassat\CurriculumVitaeBundle Symfony bundle.
 *
 * (c) Fabien Crassat <fabien@crassat.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FabienCrassat\CurriculumVitaeBundle\Tests\DependencyInjection;

use FabienCrassat\CurriculumVitaeBundle\DependencyInjection\FabienCrassatCurriculumVitaeExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser;

class FabienCrassatCurriculumVitaeExtensionTest extends \PHPUnit\Framework\TestCase
{
    /**
    * @var ContainerBuilder
    */
    private $configuration;
    private $loader;

    public function setUp()
    {
        $this->configuration = new ContainerBuilder;
        $this->loader        = new FabienCrassatCurriculumVitaeExtension;
    }

    /**
    * Tests services existence
    */
    public function testLoadDefaultParameters()
    {
        $this->createEmptyConfiguration();

        $this->assertHasParameters();

        $this->assertStringEndsWith(
            'Resources/data',
            $this->configuration->getParameter(FabienCrassatCurriculumVitaeExtension::PATH_TO_CV)
        );

        $parameters = [
            FabienCrassatCurriculumVitaeExtension::DEFAULT_CV
                => 'example',
            FabienCrassatCurriculumVitaeExtension::DEFAULT_LANG
                => 'en',
            FabienCrassatCurriculumVitaeExtension::TEMPLATE
                => '@FabienCrassatCurriculumVitae/CurriculumVitae/index.html.twig'
        ];
        $this->compareParameters($parameters);
    }

    /**
    * Tests custom mailer
    */
    public function testLoadCustomParameters()
    {
        $this->createFullConfiguration();

        $this->assertHasParameters();

        $parameters = [
            FabienCrassatCurriculumVitaeExtension::PATH_TO_CV   => './Tests/Resources/data',
            FabienCrassatCurriculumVitaeExtension::DEFAULT_CV   => 'mycv',
            FabienCrassatCurriculumVitaeExtension::DEFAULT_LANG => 'fr',
            FabienCrassatCurriculumVitaeExtension::TEMPLATE     => 'AcmeHelloBundle:CV:index.html.twig'
        ];
        $this->compareParameters($parameters);
    }

    /**
    * Creates an empty configuration
    */
    private function createEmptyConfiguration()
    {
        $this->createConfiguration($this->getEmptyConfig());
    }

    /**
    * Creates a full configuration
    */
    private function createFullConfiguration()
    {
        $this->createConfiguration($this->getFullConfig());
    }

    /**
    * Creates a configuration
    */
    private function createConfiguration($config)
    {
        $this->loader->load([$config], $this->configuration);
        $this->assertTrue($this->configuration instanceof ContainerBuilder);
    }

    /**
    * Gets an empty config
    *
    * @return String[]
    */
    private function getEmptyConfig()
    {
        $yaml   = <<<EOF
EOF;
        $parser = new Parser;

        return (array) $parser->parse($yaml);
    }

    /**
    * Gets a full config
    *
    * @return String[]
    */
    private function getFullConfig()
    {
        $yaml   = <<<EOF
path_to_cv:
    "./Tests/Resources/data"
custo_default_cv:
    "mycv"
default_lang:
    "fr"
template:
    "AcmeHelloBundle:CV:index.html.twig"
EOF;
        $parser = new Parser;

        return (array) $parser->parse($yaml);
    }

    /**
    * Asserts the identifiers matched parameters
    */
    private function assertHasParameters()
    {
        $this->assertHasParameter(FabienCrassatCurriculumVitaeExtension::PATH_TO_CV);
        $this->assertHasParameter(FabienCrassatCurriculumVitaeExtension::DEFAULT_CV);
        $this->assertHasParameter(FabienCrassatCurriculumVitaeExtension::DEFAULT_LANG);
        $this->assertHasParameter(FabienCrassatCurriculumVitaeExtension::TEMPLATE);
    }

    /**
    * Compare the identifiers matched parameters
    */
    private function compareParameters($parameters)
    {
        foreach ($parameters as $parameter => $valueToCompare) {
            $this->assertEquals(
                $this->configuration->getParameter($parameter),
                $valueToCompare
            );
        }
    }

    /**
    * Asserts the given identifier matched a parameter
    *
    * @param string $identifier
    */
    private function assertHasParameter($identifier)
    {
        $this->assertTrue($this->configuration->hasParameter($identifier));
    }
}

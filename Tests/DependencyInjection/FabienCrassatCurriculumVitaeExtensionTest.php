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

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class FabienCrassatCurriculumVitaeExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
    * @var ContainerBuilder
    */
    private $configuration;

    /**
    * Tests services existence
    */
    public function testLoadDefaultParameters()
    {
        $this->createEmptyConfiguration();

        $this->assertHasParameter('fabiencrassat_curriculumvitae.path_to_cv');
        $this->assertHasParameter('fabiencrassat_curriculumvitae.default_cv');
        $this->assertHasParameter('fabiencrassat_curriculumvitae.default_lang');
        $this->assertHasParameter('fabiencrassat_curriculumvitae.template');

        $this->assertStringEndsWith(
            'Resources/data',
            $this->configuration->getParameter('fabiencrassat_curriculumvitae.path_to_cv')
        );
        $this->assertEquals(
            $this->configuration->getParameter('fabiencrassat_curriculumvitae.default_cv'),
            'example'
        );
        $this->assertEquals(
            $this->configuration->getParameter('fabiencrassat_curriculumvitae.default_lang'),
            'en'
        );
        $this->assertEquals(
            $this->configuration->getParameter('fabiencrassat_curriculumvitae.template'),
            'FabienCrassatCurriculumVitaeBundle:CurriculumVitae:index.html.twig'
        );
    }

    /**
    * Tests custom mailer
    */
    public function testLoadCustomParameters()
    {
        $this->createFullConfiguration();

        $this->assertHasParameter('fabiencrassat_curriculumvitae.path_to_cv');
        $this->assertHasParameter('fabiencrassat_curriculumvitae.default_cv');
        $this->assertHasParameter('fabiencrassat_curriculumvitae.default_lang');
        $this->assertHasParameter('fabiencrassat_curriculumvitae.template');

        $this->assertEquals(
            './Tests/Resources/data',
            $this->configuration->getParameter('fabiencrassat_curriculumvitae.path_to_cv')
        );
        $this->assertEquals(
            $this->configuration->getParameter('fabiencrassat_curriculumvitae.default_cv'),
            'mycv'
        );
        $this->assertEquals(
            $this->configuration->getParameter('fabiencrassat_curriculumvitae.default_lang'),
            'fr'
        );
        $this->assertEquals(
            $this->configuration->getParameter('fabiencrassat_curriculumvitae.template'),
            'AcmeHelloBundle:CV:index.html.twig'
        );
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function testBadDirectory()
    {
        $this->createBadConfiguration();
    }

    /**
    * Creates an empty configuration
    */
    private function createEmptyConfiguration()
    {
        $this->configuration = new ContainerBuilder;
        $loader = new FabienCrassatCurriculumVitaeExtension;
        $config = $this->getEmptyConfig();
        $loader->load(array($config), $this->configuration);
        $this->assertTrue($this->configuration instanceof ContainerBuilder);
    }

    /**
    * Creates a full configuration
    */
    private function createFullConfiguration()
    {
        $this->configuration = new ContainerBuilder;
        $loader = new FabienCrassatCurriculumVitaeExtension;
        $config = $this->getFullConfig();
        $loader->load(array($config), $this->configuration);
        $this->assertTrue($this->configuration instanceof ContainerBuilder);
    }

    /**
    * Creates a bad configuration
    */
    private function createBadConfiguration()
    {
        $this->configuration = new ContainerBuilder;
        $loader = new FabienCrassatCurriculumVitaeExtension;
        $config = $this->getBadPathToCvConfig();
        $loader->load(array($config), $this->configuration);
        $this->assertTrue($this->configuration instanceof ContainerBuilder);
    }

    /**
    * Gets an empty config
    *
    * @return array
    */
    private function getEmptyConfig()
    {
        $yaml = <<<EOF
EOF;
        $parser = new Parser;

        return $parser->parse($yaml);
    }

    /**
    * Gets a full config
    *
    * @return array
    */
    private function getFullConfig()
    {
        $yaml = <<<EOF
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

        return $parser->parse($yaml);
    }

    /**
    * Gets a full config
    *
    * @return array
    */
    private function getBadPathToCvConfig()
    {
        $yaml = <<<EOF
path_to_cv:
    "itIsNotADirectory"
EOF;
        $parser = new Parser;

        return $parser->parse($yaml);
    }

    /**
    * Asserts the given identifier matched a parameter
    *
    * @param string $id
    */
    private function assertHasParameter($id)
    {
        $this->assertTrue($this->configuration->hasParameter($id));
    }

}

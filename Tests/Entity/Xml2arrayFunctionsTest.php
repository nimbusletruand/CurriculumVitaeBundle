<?php

/*
 * This file is part of the FabienCrassat\CurriculumVitaeBundle Symfony bundle.
 *
 * (c) Fabien Crassat <fabien@crassat.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FabienCrassat\CurriculumVitaeBundle\Tests\Entity;

use FabienCrassat\CurriculumVitaeBundle\Entity\Xml2arrayFunctions;

class Xml2arrayFunctionsTest extends \PHPUnit\Framework\TestCase
{
    private $xml2arrayFunctions;

    public function testXml2arrayEmpty() {
        $string   = <<<XML
<?xml version='1.0'?>
<document>
</document>
XML;
        $expected = array();

        $this->assertXml2Array($expected, $string, $string);
    }

    public function testXml2arrayWithBadLanguage() {
        $string   = <<<XML
<?xml version='1.0'?>
<document>
    <node lang='unknown'>something we don't want</node>
</document>
XML;
        $expected = array();

        $this->assertXml2Array($expected, $string, $string);
    }

    public function testXml2arraySimple() {
        $string   = <<<XML
<?xml version='1.0'?>
<document>
 <title>Forty What?</title>
 <from>Joe</from>
 <to>Jane</to>
 <body>
  I know that's the answer -- but what's the question?
 </body>
</document>
XML;
        $expected = array(
            'title' => 'Forty What?',
            'from'  => 'Joe',
            'to'    => 'Jane',
            'body'  => "I know that's the answer -- but what's the question?"
        );

        $this->assertXml2Array($expected, $string, $string);
    }

    public function testXml2arrayWithAttribute() {
        $string   = <<<XML
<?xml version='1.0'?>
<document>
 <attr attributekey="we don't care">The value win, not the attribute!!!</attr>
 <attr2 attributekey="attributevalue"><product>product</product></attr2>
 <onlyattribute attributekey="attributevalue"></onlyattribute>
 <value>value</value>
 <id id="id"></id>
 <ref ref="ref"></ref>
 <lang lang="lang"></lang>
</document>
XML;
        $expected = array(
            'attr' => 'The value win, not the attribute!!!',
            'onlyattribute'   => array(
                'attributekey' => 'attributevalue'),
            'attr2'   => array(
                'attributekey' => 'attributevalue',
                'product' => 'product'),
            'value'  => 'value'
        );

        $this->assertXml2Array($expected, $string, $string);
    }

    public function testXml2arrayWithCrossRefDepth1() {
        $curriculumVitae = <<<XML
<?xml version='1.0'?>
<document>
 <society crossref="societies/society[@ref='OneSociety']/*"></society>
 <societies>
  <society ref="OneSociety">
    <name>OneSociety</name>
    <address>An address</address>
    <siteurl>http://www.google.com</siteurl>
  </society>
 </societies>
</document>
XML;

        $string = <<<XML
<?xml version='1.0'?>
<document>
 <society crossref="societies/society[@ref='OneSociety']/*"></society>
</document>
XML;

        $expected = array('society' => array(
            'name'      => 'OneSociety',
            'address'   => 'An address',
            'siteurl'   => 'http://www.google.com'
        ));

        $this->assertXml2Array($expected, $curriculumVitae, $string);
    }

    public function testXml2arrayWithCrossRefDepth2() {
        $curriculumVitae = <<<XML
<?xml version='1.0'?>
<document>
 <job crossref="experience[@id='OneExperience']/*"></job>
 <experience id="OneExperience">
  <society crossref="societies/society[@ref='OneSociety']/*"></society>
  <job>My first job</job>
 </experience>
 <societies>
  <society ref="OneSociety">
    <name>OneSociety</name>
    <address>An address</address>
    <siteurl>http://www.google.com</siteurl>
  </society>
 </societies>
</document>
XML;

        $string = <<<XML
<?xml version='1.0'?>
<document>
 <job crossref="experience[@id='OneExperience']/*"></job>
</document>
XML;

        $expected = array('job' => array(
            'society' => array(
                'name'      => 'OneSociety',
                'address'   => 'An address',
                'siteurl'   => 'http://www.google.com'),
            'job'   => 'My first job'
        ));

        $this->assertXml2Array($expected, $curriculumVitae, $string);

        $string = <<<XML
<?xml version='1.0'?>
<document>
 <experience id="OneExperience">
  <society crossref="societies/society[@ref='OneSociety']/*"></society>
  <job>My first job</job>
 </experience>
</document>
XML;

        $expected = array('OneExperience' => array(
            'society' => array(
                'name'      => 'OneSociety',
                'address'   => 'An address',
                'siteurl'   => 'http://www.google.com'),
            'job'   => 'My first job'
        ));

        $this->assertXml2Array($expected, $curriculumVitae, $string);

        $string = <<<XML
<?xml version='1.0'?>
<document>
  <society crossref="societies/society[@ref='OneSociety']/*"></society>
</document>
XML;

        $expected = array('society' => array(
            'name'      => 'OneSociety',
            'address'   => 'An address',
            'siteurl'   => 'http://www.google.com')
        );

        $this->assertXml2Array($expected, $curriculumVitae, $string);
    }

    public function testXml2arrayWithCrossRef() {
        $curriculumVitae = <<<XML
<?xml version='1.0'?>
<document>
 <job crossref="experience[@id='OneExperience']/*"></job>
 <experience id="OneExperience">
  <society crossref="societies/society[@ref='OneSociety']/*"></society>
  <job>My first job</job>
 </experience>
 <societies>
  <society ref="OneSociety">
    <name>OneSociety</name>
    <address>An address</address>
    <siteurl>http://www.google.com</siteurl>
  </society>
 </societies>
</document>
XML;

        $string = <<<XML
<?xml version='1.0'?>
<document>
 <job crossref="experience[@id='OneExperience']/*"></job>
</document>
XML;

        $expected = array('job' => array(
            'society' => array(
                'name'      => 'OneSociety',
                'address'   => 'An address',
                'siteurl'   => 'http://www.google.com'),
            'job'   => 'My first job'
        ));

        $this->assertXml2Array($expected, $curriculumVitae, $string);
    }

    public function testXml2arrayWithCVCrossRef() {
        $curriculumVitae = <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>
<root>
    <langs>
        <lang id="en">English</lang><lang id="fr">Français</lang>
    </langs>
    <curriculumVitae>
        <lookingFor>
            <experience crossref="curriculumVitae/experiences/items/experience[@id='SecondJob']/*"></experience>
            <presentation lang="en">A good presentation.</presentation>
            <presentation lang="fr">Une bonne présentation.</presentation>
        </lookingFor>
        <experiences anchor="experiences">
            <anchorTitle lang="en">Experiences</anchorTitle>
            <anchorTitle lang="fr">Expériences Professionnelles</anchorTitle>
            <items>
                <experience id="SecondJob">
                    <date lang="en">Apr 2011 - Present</date>
                    <date lang="fr">Avr. 2011 - Aujourd'hui</date>
                    <job lang="en">Second Job</job>
                    <job lang="fr">Deuxième Job</job>
                    <society crossref="societies/society[@ref='OneSociety']/*"></society>
                    <missions lang="en">
                        <item>A mission of my second job.</item>
                    </missions>
                    <missions lang="fr">
                        <item>Une mission de mon deuxième job.</item>
                    </missions>
                </experience>
                <experience id="FirstJob">
                    <date lang="en">Nov 2009 - Apr 2011</date>
                    <date lang="fr">Nov. 2009 - Avr. 2011</date>
                    <job lang="en">First Job</job>
                    <job lang="fr">Premier Job</job>
                    <society crossref="societies/society[@ref='OneSociety']/*"></society>
                    <missions lang="en">
                        <item>A mission of my first job.</item>
                    </missions>
                    <missions lang="fr">
                        <item>Une mission de mon premier job.</item>
                    </missions>
                </experience>
            </items>
        </experiences>
    </curriculumVitae>
    <societies>
        <society ref="OneSociety">
            <name>OneSociety</name><address>address</address><siteurl>http://www.google.com</siteurl>
        </society>
    </societies>
</root>
XML;

        $society           = array(
            'name'    => 'OneSociety',
            'address' => 'address',
            'siteurl' => 'http://www.google.com');
        $currentExperience = array(
            'job'      => 'Second Job',
            'date'     => 'Apr 2011 - Present',
            'society'  => $society,
            'missions' => array(
                'item' => array('A mission of my second job.')));

        $expected = array(
            'langs'  => array(
                'en' => 'English', 'fr' => 'Français'),
            'curriculumVitae' => array(
                'lookingFor'  => array(
                    'experience'   => $currentExperience,
                    'presentation' => 'A good presentation.'),
                'experiences' => array(
                    'anchorTitle' => 'Experiences',
                    'items' => array(
                        'SecondJob' => $currentExperience,
                        'FirstJob'  => array(
                            'job'      => 'First Job',
                            'date'     => 'Nov 2009 - Apr 2011',
                            'society'  => $society,
                            'missions' => array(
                                'item' => array('A mission of my first job.')))),
                    'anchor' => 'experiences')),
            'societies' => array(
                'society' => $society)
        );

        $this->assertXml2Array($expected, $curriculumVitae, $curriculumVitae);
    }

    /**
     * @param string $curriculumVitae
     * @param string $xml
     */
    private function assertXml2Array($expected, $curriculumVitae, $xml) {
        $this->xml2arrayFunctions = new Xml2arrayFunctions(simplexml_load_string($curriculumVitae));

        $result = $this->xml2arrayFunctions->xml2array(simplexml_load_string($xml));

        $this->assertEquals($expected, $result);
    }
}

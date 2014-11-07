<?php

/*
 * This file is part of the FabienCrassat\CurriculumVitaeBundle Symfony bundle.
 *
 * (c) Fabien Crassat <fabien@crassat.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FabienCrassat\CurriculumVitaeBundle\Entity;

use FabienCrassat\CurriculumVitaeBundle\Utility\AgeCalculator;
use FabienCrassat\CurriculumVitaeBundle\Utility\LibXmlDisplayErrors;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

class CurriculumVitae extends Xml2arrayFunctions
{
    public $lang;
    public $CV;
    private $pathToFile;
    private $interface;
    private $file;

    /**
     * @param string $pathToFile
     */
    public function __construct($pathToFile, $lang = 'en') {
        $this->pathToFile = $pathToFile;
        $this->setFileName();
        $this->lang = $lang;
        $this->CV = $this->getXmlCurriculumVitae();
    }

    private function setFileName() {
        $data = explode("/", $this->pathToFile);
        $data = $data[count($data) - 1];
        $data = explode(".", $data);;
        $this->file = $data[0];
    }

    private function getXmlCurriculumVitae() {
        if (is_null($this->pathToFile) || !is_file($this->pathToFile)) {
            throw new InvalidArgumentException("The path " . $this->pathToFile . " is not a valid path to file.");
        }
        $this->validateXmlCurriculumVitae();

        return simplexml_load_file($this->pathToFile);
    }

    private function validateXmlCurriculumVitae() {
        // Activer "user error handling"
        libxml_use_internal_errors(TRUE);

        // Instanciation d’un DOMDocument
        $dom = new \DOMDocument("1.0");

        // Charge du XML depuis un fichier
        $dom->load($this->pathToFile);

        // Validation du document XML
        $reflClass = new \ReflectionClass(get_class($this));
        $xsdFile = dirname($reflClass->getFileName()).'/validator.xsd';
        $validate = $dom->schemaValidate($xsdFile);
        if (!$validate) {
            $libxmlDisplayErrors = new LibXmlDisplayErrors;
            throw new InvalidArgumentException($libxmlDisplayErrors->libXmlDisplayErrors());;
        }

        return $validate;
    }

    public function getDropDownLanguages() {
        $this->interface = $this->CV->{"langs"};
        $return = $this->getXMLValue();
        if(!$return) {
            $return = array($this->lang => $this->lang);
        }

        return $return;
    }

    public function getAnchors() {
        $anchorsAttribute = $this->CV->xpath("curriculumVitae/*[attribute::anchor]");

        $anchors = array();
        foreach ($anchorsAttribute as $anchorsKey => $anchorsValue) {
            $anchor = (string) $anchorsValue['anchor'];
            $title = $anchorsValue->xpath("anchorTitle[@lang='" . $this->lang . "']");
            if (count($title) == 0) {
                $title = $anchorsValue->xpath("anchorTitle");
            }
            $anchors[$anchor] = array(
                'href'  => $anchor,
                'title' => (string) $title[0],
            );
        };

        return $anchors;
    }

    public function getHumanFileName() {
        $myName = $this->getMyName();
        $myCurrentJob = $this->getMyCurrentJob();
        if (NULL != $myName) {
            if (NULL != $myCurrentJob) {
                return $myName.' - '.$myCurrentJob;
            } else {
                return $myName;
            }
        } else {
            return $this->file;
        }
    }

    private function getMyName() {
        $identity = $this->getIdentity();
        return $identity['myself']['name'];
    }

    private function getMyCurrentJob() {
        $lookingFor = $this->getLookingFor();
        if (isset($lookingFor['experience']['job'])) {
            return $lookingFor['experience']['job'];
        } elseif (isset($lookingFor['experience'])) {
            return $lookingFor['experience'];
        } else {
            return NULL;
        }
    }

    public function getIdentity() {
        $this->interface = $this->CV->curriculumVitae->identity->items;
        return $this->getXMLValue();
    }

    public function getFollowMe() {
        $this->interface = $this->CV->curriculumVitae->followMe->items;
        return $this->getXMLValue();
    }

    public function getLookingFor() {
        $this->interface = $this->CV->curriculumVitae->lookingFor;
        return $this->getXMLValue();
    }

    public function getExperiences() {
        $this->interface = $this->CV->curriculumVitae->experiences->items;
        return $this->getXMLValue();
    }

    public function getSkills() {
        $this->interface = $this->CV->curriculumVitae->skills->items;
        return $this->getXMLValue();
    }

    public function getEducations() {
        $this->interface = $this->CV->curriculumVitae->educations->items;
        return $this->getXMLValue();
    }

    public function getLanguageSkills() {
        $this->interface = $this->CV->curriculumVitae->languageSkills->items;
        return $this->getXMLValue();
    }

    public function getMiscellaneous() {
        $this->interface = $this->CV->curriculumVitae->miscellaneous->items;
        return $this->getXMLValue();
    }

    private function getXMLValue() {
        if (!$this->interface) {
            return NULL;
        } else {
            return $this->xml2array($this->interface);
        }
    }
}

class Xml2arrayFunctions {
    private $arXML;
    private $attr;
    private $key;

    public function xml2array(\SimpleXMLElement $xml, $depth = 0, $format = TRUE) {
        $depth = $depth + 1;
        $this->arXML = array();
        $this->attr = array();

        // Extraction of the node
        $this->key = trim($xml->getName());
        $value = trim((string) $xml);

        // Specific Attribute: do nothing when it is not the good language
        if ($xml->attributes()->lang) {
            if ($xml->attributes()->lang <> $this->lang) {
                return NULL;
            }
            unset($xml->attributes()->lang);
        }
        // Specific Attributes
        $this->key   = $this->setSpecificAttributeKeyWithGivenId($xml, $this->key);
        $value = $this->setSpecificAttributeAge($xml, $value);
        $this->arXML = $this->retrieveSpecificAttributeCrossRef($xml, $this->arXML, $this->key);
        // Standard Attributes
        $this->setStandardAttributes($xml);
        // Specific Key
        $value = $this->setValueForSpecificKeys($this->key, $value, $format);

        $this->setValue($value);
        $this->setAttribute();
        $this->arXML = $this->setChildren($xml, $depth, $this->key, $this->arXML);

        return $this->arXML;
    }

    /**
     * @param string $value
     */
    private function setValue($value) {
        if ($value <> '') {
            $this->arXML = array_merge($this->arXML, array($this->key => $value));
        }
    }

    private function setAttribute() {
        if (count($this->attr) > 0) {
            $this->arXML = array_merge($this->arXML, array($this->key => $this->attr));
        }
    }

    /**
     * @param integer $depth
     * @param string $key
     */
    private function setChildren(\SimpleXMLElement $xml, $depth, $key, array $arXML) {
        if ($xml->children()->count() > 0) {
            foreach($xml->children() as $childKey => $childValue) {
                $child = $this->xml2array($childValue, $depth);
                if ($depth > 1 && $child) {
                    $arXML = array_merge_recursive($arXML, array($key => $child));
                } elseif ($child) {
                    $arXML = array_merge_recursive($arXML, $child);
                }
            }
        }
        return $arXML;
    }

    private function setStandardAttributes(\SimpleXMLElement $xml) {
        // Standard Attributes (without Specific thanks to unset())
        foreach($xml->attributes() as $attributeKey => $attributeValue) {
            $this->attr[$attributeKey] = trim($attributeValue);
        }
    }

    /**
     * @param string $key
     */
    private function setSpecificAttributeKeyWithGivenId(\SimpleXMLElement $xml, $key) {
        // Specific Attribute: change the key with the given id
        if ($xml->attributes()->id) {
            $key = (string) $xml->attributes()->id;
            unset($xml->attributes()->id);
        }
        return $key;
    }

    /**
     * @param string $value
     */
    private function setSpecificAttributeAge(\SimpleXMLElement $xml, $value) {
        // Specific Attribute: Retreive the age
        if ($xml->attributes()->getAge) {
            $CVCrossRef = $this->CV->xpath(trim($xml->attributes()->getAge));
            $cr = $this->xml2array($CVCrossRef[0], NULL, FALSE);
            $cr = implode("", $cr);
            $AgeCalculator = new AgeCalculator((string) $cr);
            $value = $AgeCalculator->age();
            unset($xml->attributes()->getAge);
        }
        return $value;
    }

    /**
     * @param string $key
     */
    private function retrieveSpecificAttributeCrossRef(\SimpleXMLElement $xml, array $arXML, $key) {
        // Specific Attribute: Retrieve the given crossref
        if ($xml->attributes()->crossref) {
            $CVCrossRef = $this->CV->xpath(trim($xml->attributes()->crossref));
            $cr = $this->xml2array($CVCrossRef[0]);
            $arXML = array_merge($arXML, array($key => $cr));
            unset($xml->attributes()->crossref);
        }
        return $arXML;
    }

    /**
     * @param string $key
     * @param boolean $format
     */
    private function setValueForSpecificKeys($key, $value, $format) {
        if ($key == 'birthday') {
            return $this->setValueForBirthdayKey($value, $format);
        }
        elseif ($key == "item") {
            return $this->setValueForItemKey($value);
        }
        else {
            return $value;
        }
    }

    /**
     * Specific Key: Format to french date format
     *
     * @param boolean $format
     *
     * @return string
     */
    private function setValueForBirthdayKey($value, $format) {
        if ($format) {
            setlocale(LC_TIME, array('fra_fra', 'fr', 'fr_FR', 'fr_FR.UTF8'));
            $value = strftime('%d %B %Y', strtotime(date($value)));
        }

        return $value;
    }

    /**
     * Specific Key: convert to apply array_merge()
     *
     * @return array
     */
    private function setValueForItemKey($value) {
        return array($value);
    }
}

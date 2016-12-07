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

use FabienCrassat\CurriculumVitaeBundle\Utility\LibXmlDisplayErrors;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

class CurriculumVitae extends Xml2arrayFunctions
{
    private $lang;
    private $curriculumVitae;
    private $pathToFile;
    private $interface;
    private $file;
    private $xml2arrayFunctions;

    /**
     * @param string $pathToFile
     * @param string $lang
     */
    public function __construct($pathToFile, $lang = 'en') {
        $this->pathToFile = $pathToFile;
        $this->setFileName();
        $this->lang = $lang;
        $this->curriculumVitae = $this->getXmlCurriculumVitae();
        $this->xml2arrayFunctions = New Xml2arrayFunctions($this->curriculumVitae, $this->lang);
    }

    /**
     * @return null|array
     */
    public function getDropDownLanguages() {
        $this->interface = $this->curriculumVitae->{"langs"};
        $return = $this->getXMLValue();
        if(!$return) {
            $return = array($this->lang => $this->lang);
        }

        return $return;
    }

    /**
     * @return array
     */
    public function getAnchors() {
        $anchorsAttribute = $this->curriculumVitae->xpath("curriculumVitae/*[attribute::anchor]");

        $anchors = array();
        foreach ($anchorsAttribute as $anchorsValue) {
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

    /**
     * @return string
     */
    public function getHumanFileName() {
        $myName = $this->getMyName();
        $myCurrentJob = $this->getMyCurrentJob();
        if (NULL != $myName) {
            if (NULL !== $myCurrentJob) {
                return $myName.' - '.$myCurrentJob;
            }

            return $myName;
        }
        
        return $this->file;
    }

    /**
     * @return null|array
     */
    public function getIdentity() {
        $this->interface = $this->curriculumVitae->curriculumVitae->identity->items;
        return $this->getXMLValue();
    }

    /**
     * @return null|array
     */
    public function getFollowMe() {
        $this->interface = $this->curriculumVitae->curriculumVitae->followMe->items;
        return $this->getXMLValue();
    }

    /**
     * @return null|array
     */
    public function getLookingFor() {
        $this->interface = $this->curriculumVitae->curriculumVitae->lookingFor;
        return $this->getXMLValue();
    }

    /**
     * @return null|array
     */
    public function getExperiences() {
        $this->interface = $this->curriculumVitae->curriculumVitae->experiences->items;
        return $this->getXMLValue();
    }

    /**
     * @return null|array
     */
    public function getSkills() {
        $this->interface = $this->curriculumVitae->curriculumVitae->skills->items;
        return $this->getXMLValue();
    }

    /**
     * @return null|array
     */
    public function getEducations() {
        $this->interface = $this->curriculumVitae->curriculumVitae->educations->items;
        return $this->getXMLValue();
    }

    /**
     * @return null|array
     */
    public function getLanguageSkills() {
        $this->interface = $this->curriculumVitae->curriculumVitae->languageSkills->items;
        return $this->getXMLValue();
    }

    /**
     * @return null|array
     */
    public function getMiscellaneous() {
        $this->interface = $this->curriculumVitae->curriculumVitae->miscellaneous->items;
        return $this->getXMLValue();
    }

    private function setFileName() {
        $data = explode("/", $this->pathToFile);
        $data = $data[count($data) - 1];
        $data = explode(".", $data);;
        $this->file = $data[0];
    }

    /**
     * @return string
     */
    private function getMyName() {
        $identity = $this->getIdentity();
        return $identity['myself']['name'];
    }

    /**
     * @return null|string
     */
    private function getMyCurrentJob() {
        $lookingFor = $this->getLookingFor();
        if (isset($lookingFor['experience']['job'])) {
            return $lookingFor['experience']['job'];
        } elseif (isset($lookingFor['experience'])) {
            return $lookingFor['experience'];
        }
        
        return NULL;
    }

    /**
     * @return \SimpleXMLElement
     */
    private function getXmlCurriculumVitae() {
        if (is_null($this->pathToFile) || !is_file($this->pathToFile)) {
            throw new InvalidArgumentException("The path " . $this->pathToFile . " is not a valid path to file.");
        }
        $this->validateXmlCurriculumVitae();

        return simplexml_load_file($this->pathToFile);
    }

    /**
     * @return boolean
     */
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

    /**
     * @return null|array
     */
    private function getXMLValue() {
        if (!$this->interface) {
            return NULL;
        }
        
        return $this->xml2arrayFunctions->xml2array($this->interface);
    }
}

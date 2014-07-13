<?php

/*
 * This file is part of the FabienCrassat\CurriculumVitaeBundle Symfony bundle.
 *
 * (c) Fabien Crassat <fabien@crassat.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FabienCrassat\CurriculumVitaeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use FabienCrassat\CurriculumVitaeBundle\Entity\CurriculumVitae;

class DefaultController extends Controller
{
    private $Lang;
    private $ReadCVXml;
    private $FileToLoad;
    private $exposedLanguages;
    private $hasSnappyPDF;

    public function indexAction($cvxmlfile = null)
    {
        $this->fileToLoad($cvxmlfile);
        $this->lang();
        $this->readCVFile();

        if($cvxmlfile) {
            $path = array(
                '_controller' => 'FabienCrassatCurriculumVitaeBundle:Default:display',
                'cvxmlfile'   => $this->FileToLoad,
                '_locale'     => $this->Lang,
            );
            $request = $this->container->get('request');
            $subRequest = $request->duplicate(array(), null, $path);

            $httpKernel = $this->container->get('http_kernel');
            $response = $httpKernel->handle(
                $subRequest,
                HttpKernelInterface::SUB_REQUEST
            );
            return $response;
        } else {
            return new RedirectResponse($this->generateUrl(
                'fabiencrassat_curriculumvitae_cvxmlfileonly',
                array(
                    'cvxmlfile'   => $this->FileToLoad,
                )), 301);
        }
    }

    public function displayAction($cvxmlfile, $_locale)
    {
        $this->fileToLoad($cvxmlfile);
        $this->lang($_locale);
        $this->readCVFile();

        $templateVariables = array_merge($this->defineCVViewVariables(), array('hasSnappyPDF' => $this->hasSnappyPDF));
        return $this->container->get('templating')->renderResponse(
            $this->container->getParameter('fabiencrassat_curriculumvitae.template'), $templateVariables);
    }

    public function exportPDFAction($cvxmlfile, $_locale)
    {
        $this->fileToLoad($cvxmlfile);
        $this->lang($_locale);
        $this->readCVFile();

        if (!$this->hasSnappyPDF) {
            throw new NotFoundHttpException('knp_snappy.pdf is non-existent');
        };

        $html = $this->container->get('templating')->render(
            "FabienCrassatCurriculumVitaeBundle:CurriculumVitae:index.pdf.twig",  $this->defineCVViewVariables());

        $identity = $this->ReadCVXml->getIdentity();
        $lookingFor = $this->ReadCVXml->getLookingFor();
        $filename = $identity['myself']['Name'].' - '.$lookingFor['experience']['job'].'.pdf';

        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
            200,
            array(
                'Content-Type'          => 'application/pdf',
                'Content-Disposition'   => 'attachment; filename="'.$filename.'"'
            )
        );
    }

    private function fileToLoad($cvxmlfile = null) {
        if (!$cvxmlfile) {
            // Retreive the CV file depending the configuration
            $custo_default_cv = $this->container->getParameter('fabiencrassat_curriculumvitae.custo_default_cv');
            if(is_null($custo_default_cv)) {
                $cvxmlfile = $this->container->getParameter('fabiencrassat_curriculumvitae.default_cv');
            } else {
                $cvxmlfile = $custo_default_cv;
            }
        }

        $this->FileToLoad = (string) $cvxmlfile;
    }

    private function lang($_locale = null)
    {
        if (!$_locale) {
            $_locale = $this->container->getParameter('fabiencrassat_curriculumvitae.default_lang');
        }

        $this->Lang = (string) $_locale;
    }

    private function readCVFile()
    {
        // Check the file in the filesystem
        $pathToFile = $this->container->getParameter('fabiencrassat_curriculumvitae.path_to_cv').'/'.$this->FileToLoad.'.xml';
        if (!is_file($pathToFile)) {
            throw new NotFoundHttpException('There is no curriculum vitae file defined for '.$this->FileToLoad.' ('.$pathToFile.').');
        }

        // Read the Curriculum Vitae
        $this->ReadCVXml = new CurriculumVitae($pathToFile, $this->Lang);

        // Check if there is at least 1 language defined
        $this->exposedLanguages = $this->ReadCVXml->getDropDownLanguages();
        if(is_array($this->exposedLanguages)) {
        if (!array_key_exists($this->Lang, $this->exposedLanguages)) {
            throw new NotFoundHttpException('There is no curriculum vitae defined for this language');
        }};

        // Check if knp_snappy is existent
        $this->hasSnappyPDF = $this->container->has('knp_snappy.pdf');
    }
    private function defineCVViewVariables()
    {
        return array(
                'cvxmlfile'         => $this->FileToLoad,
                'languageView'      => $this->Lang,
                'languages'         => $this->exposedLanguages,
                'anchors'           => $this->ReadCVXml->getAnchors(),
                'identity'          => $this->ReadCVXml->getIdentity(),
                'followMe'          => $this->ReadCVXml->getFollowMe(),
                'lookingFor'        => $this->ReadCVXml->getLookingFor(),
                'experiences'       => $this->ReadCVXml->getExperiences(),
                'skills'            => $this->ReadCVXml->getSkills(),
                'educations'        => $this->ReadCVXml->getEducations(),
                'languageSkills'    => $this->ReadCVXml->getLanguageSkills(),
                'miscellaneous'     => $this->ReadCVXml->getMiscellaneous(),
                'society'           => $this->ReadCVXml->getSociety()
        );
    }
};
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

use FabienCrassat\CurriculumVitaeBundle\Entity\CurriculumVitae;
use FabienCrassat\CurriculumVitaeBundle\Utility\Tools;

class CurriculumVitaeTest extends \PHPUnit_Framework_TestCase
{
    private $CV;

    public function __construct() {
        $this->tools = new Tools();
    }

    public function testNoLanguage() {
        $this->CV = new CurriculumVitae(__DIR__.'/../Resources/data/core.xml');
        $language = $this->CV->getDropDownLanguages();
        if (is_array($language)) {
            $this->assertEquals(0,
                $this->tools->arraysAreSimilar(array('en' => 'en'), $language)
            );
        }
    }

    public function testSimpleHumanFileName() {
        $this->CV = new CurriculumVitae(__DIR__.'/../Resources/data/core.xml');
        $this->assertSame("core", $this->CV->getHumanFileName());
    }

    public function testHumanFileNameWithExperience() {
        $this->CV = new CurriculumVitae(__DIR__.'/../../Resources/data/example.xml');
        $this->assertSame("First Name Last Name - Curriculum Vitae Title",
            $this->CV->getHumanFileName()
        );
    }

    public function testHumanFileNameWithJob() {
        $this->CV = new CurriculumVitae(__DIR__.'/../Resources/data/test.xml');
        $this->assertSame("First Name Last Name - The job", $this->CV->getHumanFileName());
    }

    public function testHumanFileNameWithOnLyName(){
        $this->CV = new CurriculumVitae(__DIR__.'/../Resources/data/justIdentityMySelf.xml');
        $this->assertSame("First Name Last Name", $this->CV->getHumanFileName());
    }

    public function testNullReturnWithNoDeclarationInCurriculumVitaeTag() {
        $this->CV = new CurriculumVitae(__DIR__.'/../Resources/data/core.xml');
        $this->assertNull($this->CV->getIdentity());
    }

    public function testGetAnchorsWithNoLang() {
        $this->CV = new CurriculumVitae(__DIR__.'/../Resources/data/test.xml');
        $anchors = $this->CV->getAnchors();
        if (is_array($anchors)) {
            $this->assertEquals(0, $this->tools->arraysAreSimilar(
                array('identity' => array(
                        'href' => 'identity',
                        'title' => 'identity'),
                      'followMe' => array(
                        'href' => 'followMe',
                        'title' => 'followMe'),
                      'experiences' => array(
                        'href' => 'experiences',
                        'title' => 'experiences'),
                      'skills' => array(
                        'href' => 'skills',
                        'title' => 'skills'),
                      'educations' => array(
                        'href' => 'educations',
                        'title' => 'educations'),
                      'languageSkills' => array(
                        'href' => 'languageSkills',
                        'title' => 'languageSkills'),
                      'miscellaneous' => array(
                        'href' => 'miscellaneous',
                        'title' => 'miscellaneous')
                ),
                $anchors
            ));
        }
    }

    public function testGetIdentityWithEnglishLanguage() {
        $this->CV = new CurriculumVitae(__DIR__.'/../../Resources/data/example.xml');
        $compare = $this->tools->arraysAreSimilar($this->CV->getIdentity(), array(
            'myself' => array(
                'name' => 'First Name Last Name',
                'birthday' => '01 janvier 1975' ,
                'age' => 39,
                'nationality' => 'French Citizenship',
                'picture' => 'bundles/fabiencrassatcurriculumvitae/img/example.png'
            ),
            'address' => array(
                'street' => 'Street',
                'postalcode' => 'PostalCode',
                'city' => 'City',
                'country' => 'Country',
                'googlemap' => 'http://maps.google.com'
            ),
            'contact' => array(
                'mobile' => 'Telephone',
                'email' => 'email_arobase_site_dot_com'
            ),
            'social' => array(
                'drivelicences' => 'French driving licence'
            )
        ));
        if ($compare <> 0) {
            var_dump($compare);
        }
        $this->assertEquals(0, $compare);
    }

    public function testGetIdentityWithFrenchLanguage() {
        $this->CV = new CurriculumVitae(__DIR__.'/../../Resources/data/example.xml', "fr");
        $compare = $this->tools->arraysAreSimilar($this->CV->getIdentity(), array(
            'myself' => array(
                'name' => 'First Name Last Name',
                'birthday' => '01 janvier 1975' ,
                'birthplace' => 'Paris',
                'picture' => 'bundles/fabiencrassatcurriculumvitae/img/example.png'
            ),
            'address' => array(
                'street' => 'Street',
                'postalcode' => 'PostalCode',
                'city' => 'City',
                'country' => 'Country',
                'googlemap' => 'http://maps.google.com'
            ),
            'contact' => array(
                'mobile' => 'Telephone',
                'email' => 'email_arobase_site_dot_com'
            ),
            'social' => array(
                'marital' => 'Célibataire',
                'military' => 'Dégagé des obligations militaires',
                'drivelicences' => 'Titulaire du permis B'
            )
        ));
        if ($compare <> 0) {
            var_dump($compare);
        }
        $this->assertEquals(0, $compare);
    }

    public function testGetFollowMeWithEnglishLanguage() {
        $this->CV = new CurriculumVitae(__DIR__.'/../../Resources/data/example.xml');
        $this->assertEquals(0, $this->tools->arraysAreSimilar(array(
            'linkedin' => array(
                'title' => 'Linked In',
                'url'   => 'http://www.linkedin.com',
                'icon'  => 'bundles/fabiencrassatcurriculumvitae/img/linkedin.png'
            ),
            'viadeo' => array(
                'title' => 'Viadeo',
                'url'   => 'http://www.viadeo.com',
                'icon'  => 'bundles/fabiencrassatcurriculumvitae/img/viadeo.png'
            ),
            'monster' => array(
                'title' => 'Monster',
                'url'   => 'http://beknown.com',
                'icon'  => 'bundles/fabiencrassatcurriculumvitae/img/monster.png'
            ),
            'twitter' => array(
                'title' => 'Twitter',
                'url'   => 'https://twitter.com',
                'icon'  => 'bundles/fabiencrassatcurriculumvitae/img/twitter.png'
            ),
            'googleplus' => array(
                'title' => 'Google+',
                'url'   => 'https://plus.google.com',
                'icon'  => 'bundles/fabiencrassatcurriculumvitae/img/googleplus.png'
            ),
            'facebook' => array(
                'title' => 'Facebook',
                'url'   => 'https://www.facebook.com',
                'icon'  => 'bundles/fabiencrassatcurriculumvitae/img/facebook.png'
            ),
            'scrum' => array(
                'title' => 'Scrum',
                'url'   => 'http://www.scrumalliance.org',
                'icon'  => 'bundles/fabiencrassatcurriculumvitae/img/scrum-alliance.png'
            )),
            $this->CV->getFollowMe()
        ));
    }

    public function testGetFollowMeWithFrenchLanguage() {
        $this->CV = new CurriculumVitae(__DIR__.'/../../Resources/data/example.xml', "fr");
        $this->assertEquals(0, $this->tools->arraysAreSimilar(array(
            'linkedin' => array(
                'title' => 'Linked In',
                'url'   => 'http://www.linkedin.com',
                'icon'  => 'bundles/fabiencrassatcurriculumvitae/img/linkedin.png'
            ),
            'viadeo' => array(
                'title' => 'Viadeo',
                'url'   => 'http://www.viadeo.com',
                'icon'  => 'bundles/fabiencrassatcurriculumvitae/img/viadeo.png'
            ),
            'monster' => array(
                'title' => 'Monster',
                'url'   => 'http://beknown.com',
                'icon'  => 'bundles/fabiencrassatcurriculumvitae/img/monster.png'
            ),
            'twitter' => array(
                'title' => 'Twitter',
                'url'   => 'https://twitter.com',
                'icon'  => 'bundles/fabiencrassatcurriculumvitae/img/twitter.png'
            ),
            'googleplus' => array(
                'title' => 'Google+',
                'url'   => 'https://plus.google.com',
                'icon'  => 'bundles/fabiencrassatcurriculumvitae/img/googleplus.png'
            ),
            'facebook' => array(
                'title' => 'Facebook',
                'url'   => 'https://www.facebook.com',
                'icon'  => 'bundles/fabiencrassatcurriculumvitae/img/facebook.png'
            ),
            'scrum' => array(
                'title' => 'Scrum',
                'url'   => 'http://www.scrumalliance.org',
                'icon'  => 'bundles/fabiencrassatcurriculumvitae/img/scrum-alliance.png'
            )),
            $this->CV->getFollowMe()
        ));
    }

    public function testGetLookingForWithEnglishLanguage() {
        $this->CV = new CurriculumVitae(__DIR__.'/../../Resources/data/example.xml');
        $this->assertEquals(0, $this->tools->arraysAreSimilar(array(
            'experience'   => "Curriculum Vitae Title",
            'presentation' => "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean eu lectus facilisis, posuere leo laoreet, dignissim ligula. Praesent ultricies dignissim diam vitae dictum. Donec sed nisi tortor. Proin tempus scelerisque lectus, sit amet convallis mi semper a. Integer blandit a ligula a volutpat. Ut dolor eros, interdum quis ante ac, tempus commodo odio. Suspendisse ut nisi purus. Mauris vestibulum nibh sit amet turpis consequat pharetra. Duis at adipiscing risus. Vivamus vitae orci ac felis porta euismod. Fusce sit amet metus sem. Maecenas suscipit tincidunt ante, sed feugiat odio eleifend eu. Sed eu ultricies ipsum. In cursus tincidunt elit a gravida. Nam eu aliquet leo. Maecenas nibh leo, eleifend fermentum neque sit amet, viverra consequat lorem.",
            ),
            $this->CV->getLookingFor()
        ));
    }

    public function testGetLookingForWithFrenchLanguage() {
        $this->CV = new CurriculumVitae(__DIR__.'/../../Resources/data/example.xml', "fr");
        $this->assertEquals(0, $this->tools->arraysAreSimilar(array(
            'experience'   => "Titre du curriculum vitae",
            'presentation' => "Mauris rutrum justo ac bibendum ultrices. Mauris a dolor a diam tempus ornare vel non urna. Donec a dui vel nunc ultrices porta non vitae felis. Ut blandit ullamcorper orci. Quisque quis justo vitae nisl auctor laoreet non eget mauris. Sed volutpat enim est, vitae vulputate nibh laoreet gravida. Duis nec tincidunt ante. Nullam metus turpis, accumsan nec laoreet et, consectetur et ligula. Curabitur convallis feugiat lorem, sit amet tincidunt arcu sollicitudin vel. Aliquam erat volutpat. In odio elit, accumsan in facilisis at, ultricies quis justo.",
            ),
            $this->CV->getLookingFor()
        ));
    }

    public function testGetExperiencesWithEnglishLanguage() {
        $this->CV = new CurriculumVitae(__DIR__.'/../../Resources/data/example.xml');
        $compare = $this->tools->arraysAreSimilar(array(
            'FirstExperience' => array(
                'date' => 'Jan 2007 - Present',
                'job' => 'My current job',
                'society' => array(
                    'society' => array('ref' => 'MyCompany'),
                    'name' => 'My Company',
                    'address' => 'the address of the company',
                    'siteurl' => 'http://www.MyCompany.com',
                ),
                'missions' => array(
                    'item' => array(
                        0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                        1 => 'Suspendisse nec mauris eu orci dapibus mollis ac ac mi.'
                    )
                )
            ),
            'SecondExperience' => array(
                'collapse' => 'false',
                'date' => 'Sept - Dec 2006',
                'job' => 'My previous job',
                'society' => array(
                    'society' => array('ref' => 'MyOtherCompany'),
                    'name' => 'My Other Company',
                    'address' => 'the address of the company',
                    'siteurl' => 'http://www.MyOtherCompany.com',
                )
            ),
            'ThirdExperience' => array(
                'date' => 'Summer 2006',
                'job' => 'A summer job',
                'society' => array(
                    'society' => array('ref' => 'ACompanyWithoutSite'),
                    'name' => 'A company wihtout site',
                    'address' => 'the address of the company'
                ),
                'missions' => array(
                    'item' => array(
                        0 => 'Suspendisse et arcu eget est feugiat elementum.'
                    )
                )
            ),
            'FourthExperience' => array(
                'collapse' => 'true',
                'date' => 'Before 2006',
                'job' => 'The job of my life',
                'society' => 'A society with a name per language',
                'missions' => array(
                    'item' => array(
                        0 => 'Suspendisse et arcu eget est feugiat elementum.'
                    )
                )
            )
        ), $this->CV->getExperiences());
        $this->assertEquals(0, $compare);
    }

    public function testGetExperiencesWithFrenchLanguage() {
        $this->CV = new CurriculumVitae(__DIR__.'/../../Resources/data/example.xml', "fr");
        $this->assertEquals(0, $this->tools->arraysAreSimilar(array(
            'FirstExperience' => array(
                'date' => 'Jan. 2007 - Aujourd\'hui',
                'job' => 'Mon poste actuel',
                'society' => array(
                    'society' => array('ref' => 'MyCompany'),
                    'name' => 'My Company',
                    'address' => 'the address of the company',
                    'siteurl' => 'http://www.MyCompany.com',
                ),
                'missions' => array(
                    'item' => array(
                        0 => 'Donec gravida enim viverra tempor dignissim.',
                        1 => 'Sed a eros at mauris placerat adipiscing.'
                    )
                )
            ),
            'SecondExperience' => array(
                'collapse' => 'false',
                'date' => 'Sept - Dec 2006',
                'job' => 'Mon poste précédent',
                'society' => array(
                    'society' => array('ref' => 'MyOtherCompany'),
                    'name' => 'Mon autre compagnie',
                    'address' => 'l\'adresse de la compagnie',
                    'siteurl' => 'http://www.MyOtherCompany.com',
                )
            ),
            'ThirdExperience' => array(
                'date' => 'Summer 2006',
                'job' => 'Un travail d\'été',
                'society' => array(
                    'society' => array('ref' => 'ACompanyWithoutSite'),
                    'name' => 'Une compagnie sans site',
                    'address' => 'l\'adresse de la compagnie'
                ),
                'missions' => array(
                    'item' => array(
                        0 => 'Suspendisse et arcu eget est feugiat elementum.'
                    )
                )
            ),
            'FourthExperience' => array(
                'collapse' => 'true',
                'date' => 'Before 2006',
                'job' => 'Le job de ma vie',
                'society' => 'Une société avec un nom par langue',
                'missions' => array(
                    'item' => array(
                        0 => 'Suspendisse et arcu eget est feugiat elementum.'
                    )
                )
            )),
            $this->CV->getExperiences()
        ));
    }

    public function testGetSkillsWithEnglishLanguage() {
        $this->CV = new CurriculumVitae(__DIR__.'/../../Resources/data/example.xml');
        $compare = $this->tools->arraysAreSimilar(array(
            'Functional' => array(
                'title' => 'Skills',
                'lines' => array(
                    'success' => array(
                        'percentage' => 90,
                        'class' => 'success',
                        'striped' => 'true',
                        'label' => 'Increasing Skills',
                    ),
                    'otherSucess' => array(
                        'percentage' => 90,
                        'class' => 'success',
                        'label' => 'success',
                    ),
                    'info' => array(
                        'percentage' => 40,
                        'class' => 'info',
                        'striped' => 'false',
                        'label' => 'info',
                    ),
                    'warning' => array(
                        'percentage' => 20,
                        'class' => 'warning',
                        'label' => 'warning',
                    ),
                    'danger' => array(
                        'percentage' => 10,
                        'class' => 'danger',
                        'label' => 'danger',
                    ),
                    'noClass' => array(
                        'percentage' => 5,
                        'label' => 'noClass',
                    ),
                    'nothing' => array(
                        'label' => 'nothing',
                    )
                )
            ),
            'OtherSkill' => array(
                'title' => 'One other',
                'lines' => array(
                    'success' => array(
                        'percentage' => 90,
                        'class' => 'success',
                        'striped' => 'false',
                        'label' => 'Skills List',
                    ),
                    'info' => array(
                        'percentage' => 40,
                        'class' => 'info',
                        'striped' => 'false',
                        'label' => 'Label',
                    ),
                    'warning' => array(
                        'percentage' => 20,
                        'class' => 'warning',
                        'striped' => 'false',
                        'label' => 'Label',
                    ),
                    'danger' => array(
                        'percentage' => 10,
                        'class' => 'danger',
                        'striped' => 'true',
                        'label' => 'Label',
                    )
                )
            )
        ), $this->CV->getSkills());
        $this->assertEquals(0, $compare);
    }

    public function testGetSkillsWithFrenchLanguage() {
        $this->CV = new CurriculumVitae(__DIR__.'/../../Resources/data/example.xml', "fr");
        $compare = $this->tools->arraysAreSimilar(array(
            'Functional' => array(
                'title' => 'Compétences',
                'lines' => array(
                    'success' => array(
                        'percentage' => 90,
                        'class' => 'success',
                        'striped' => 'true',
                        'label' => 'Compétences grandissantes',
                    ),
                    'otherSucess' => array(
                        'percentage' => 90,
                        'class' => 'success',
                        'label' => 'success',
                    ),
                    'info' => array(
                        'percentage' => 40,
                        'class' => 'info',
                        'striped' => 'false',
                        'label' => 'info',
                    ),
                    'warning' => array(
                        'percentage' => 20,
                        'class' => 'warning',
                        'label' => 'warning',
                    ),
                    'danger' => array(
                        'percentage' => 10,
                        'class' => 'danger',
                        'label' => 'danger',
                    ),
                    'noClass' => array(
                        'percentage' => 5,
                        'label' => 'noClass',
                    ),
                    'nothing' => array(
                        'label' => 'nothing',
                    )
                )
            ),
            'OtherSkill' => array(
                'title' => 'Une autre',
                'lines' => array(
                    'success' => array(
                        'percentage' => 90,
                        'class' => 'success',
                        'striped' => 'false',
                        'label' => 'Liste de Compétences',
                    ),
                    'info' => array(
                        'percentage' => 40,
                        'class' => 'info',
                        'striped' => 'false',
                        'label' => 'Label',
                    ),
                    'warning' => array(
                        'percentage' => 20,
                        'class' => 'warning',
                        'striped' => 'false',
                        'label' => 'Label',
                    ),
                    'danger' => array(
                        'percentage' => 10,
                        'class' => 'danger',
                        'striped' => 'true',
                        'label' => 'Label',
                    )
                )
            )
        ), $this->CV->getSkills());
        $this->assertEquals(0, $compare);
    }

    public function testGetEducationsWithEnglishLanguage() {
        $this->CV = new CurriculumVitae(__DIR__.'/../../Resources/data/example.xml');
        $compare = $this->tools->arraysAreSimilar(array(
            'University' => array(
                'date' => '2002 - 2005',
                'education' => 'My diploma in my university',
                'descriptions' => array('item' => array(
                    0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc in auctor ipsum. Nullam venenatis sem.'
                ))
            ),
            'HighSchool' => array(
                'collapse' => 'false',
                'date' => 'June 2002',
                'education' => 'My diploma in my high school',
                'descriptions' => array('item' => array(
                    0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris elit dui, faucibus non laoreet luctus, dignissim at lectus. Quisque dignissim imperdiet consectetur. Praesent scelerisque neque.',
                    1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed pretium varius est sit amet consectetur. Suspendisse cursus dapibus egestas. Ut id augue quis mi scelerisque.'
                ))
            ),
            'FirstSchool' => array(
                'collapse' => 'true',
                'date' => 'June 2000',
                'education' => 'My diploma in my first school'
            )
        ), $this->CV->getEducations());
        $this->assertEquals(0, $compare);
    }

    public function testGetEducationsWithFrenchLanguage() {
        $this->CV = new CurriculumVitae(__DIR__.'/../../Resources/data/example.xml', "fr");
        $compare = $this->tools->arraysAreSimilar(array(
            'University' => array(
                'date' => '2002 - 2005',
                'education' => 'Mon diplôme dans mon université',
                'descriptions' => array('item' => array(
                    0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris elit dui, faucibus non laoreet luctus, dignissim at lectus. Quisque dignissim imperdiet consectetur. Praesent scelerisque neque.',
                    1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed pretium varius est sit amet consectetur. Suspendisse cursus dapibus egestas. Ut id augue quis mi scelerisque.'
                ))
            ),
            'HighSchool' => array(
                'collapse' => 'false',
                'date' => 'Juin 2002',
                'education' => 'Mon diplôme dans mon lycée',
                'descriptions' => array('item' => array(
                    0 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc in auctor ipsum. Nullam venenatis sem.'
                ))
            ),
            'FirstSchool' => array(
                'collapse' => 'true',
                'date' => 'Juin 2000',
                'education' => 'Mon diplôme dans mon collège'
            )
        ), $this->CV->getEducations());
        $this->assertEquals(0, $compare);
    }

    public function testGetLanguageSkillsWithEnglishLanguage() {
        $this->CV = new CurriculumVitae(__DIR__.'/../../Resources/data/example.xml');
        $compare = $this->tools->arraysAreSimilar(array(
            'French' => array(
                'title' => 'French',
                'description' => 'Level of the skill.',
                'icon' => 'bundles/fabiencrassatcurriculumvitae/img/Flag-of-France.png'
            ),
            'English' => array(
                'title' => 'English',
                'description' => 'Level of the skill.',
                'icon' => 'bundles/fabiencrassatcurriculumvitae/img/Flag-of-United-Kingdom.png'
            )
        ), $this->CV->getLanguageSkills());
        $this->assertEquals(0, $compare);
    }

    public function testGetLanguageSkillsWithFrenchLanguage() {
        $this->CV = new CurriculumVitae(__DIR__.'/../../Resources/data/example.xml', "fr");
        $compare = $this->tools->arraysAreSimilar(array(
            'French' => array(
                'title' => 'Français',
                'description' => 'Niveau',
                'icon' => 'bundles/fabiencrassatcurriculumvitae/img/Flag-of-France.png'
            ),
            'English' => array(
                'title' => 'Anglais',
                'description' => 'Niveau',
                'icon' => 'bundles/fabiencrassatcurriculumvitae/img/Flag-of-United-Kingdom.png'
            )
        ), $this->CV->getLanguageSkills());
        $this->assertEquals(0, $compare);
    }

    public function testGetMiscellaneousWithEnglishLanguage() {
        $this->CV = new CurriculumVitae(__DIR__.'/../../Resources/data/example.xml');
        $compare = $this->tools->arraysAreSimilar(array(
            'Practical' => array(
                'title' => 'Practices',
                'miscellaneous' => 'My practices'
            )
        ), $this->CV->getMiscellaneous());
        $this->assertEquals(0, $compare);
    }

    public function testGetMiscellaneousWithFrenchLanguage() {
        $this->CV = new CurriculumVitae(__DIR__.'/../../Resources/data/example.xml', "fr");
        $compare = $this->tools->arraysAreSimilar(array(
            'Practical' => array(
                'title' => 'Pratiques',
                'miscellaneous' => 'Mes pratiques',
                'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur nec auctor nisl, eu fringilla nisi. Morbi scelerisque, est vitae mattis faucibus, felis sapien lobortis augue.'
            )
        ), $this->CV->getMiscellaneous());
        $this->assertEquals(0, $compare);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidArgumentExceptionWithBadCurriculumVitaeFile() {
        $this->CV = new CurriculumVitae("abd file");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidArgumentExceptionWithNoValidXMLFile() {
        $this->CV = new CurriculumVitae( __DIR__.'/../Resources/data/empty.xml');
        $this->CV->getDropDownLanguages();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidArgumentExceptionWithFatalErrorXMLFile() {
        $this->CV = new CurriculumVitae(__DIR__.'/../Resources/data/fatalerror.xml');
        $this->CV->getDropDownLanguages();
    }
}
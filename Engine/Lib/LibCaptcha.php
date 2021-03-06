<?php

namespace PassionEngine\Engine\Lib;

use PassionEngine\Engine\Core\CoreLogger;
use PassionEngine\Engine\Core\CoreMain;
use PassionEngine\Engine\Core\CoreLayout;
use PassionEngine\Engine\Core\CoreSession;
use PassionEngine\Engine\Core\CoreRequest;
use PassionEngine\Engine\Core\CoreRequestType;
use PassionEngine\Engine\Exec\ExecCrypt;
use PassionEngine\Engine\Exec\ExecCookie;
use PassionEngine\Engine\Exec\ExecString;

/**
 * Générateur de captcha.
 *
 * @author Sébastien Villemain
 */
class LibCaptcha
{

    /**
     * Vérifie l'état initialisation de la classe.
     *
     * @var bool
     */
    private static $iniRand = false;

    /**
     * Active le script captcha.
     *
     * @var bool
     */
    private $enabled = false;

    /**
     * Un objet utiliser suivant le type.
     *
     * @var object
     */
    private $object = '';

    /**
     * La réponse correcte à donner.
     *
     * @var string
     */
    private $response = '';

    /**
     * Nom du champs en entrée.
     *
     * @var string
     */
    private $inputRobotName = '';

    /**
     * Question poser lié à la réponse courante.
     *
     * @var string
     */
    private $question = '';

    /**
     * Configuration d'un nouveau captcha
     *
     * @param mixed $object (LibForm par exemple)
     */
    public function __construct(&$object = null)
    {
        // Mode du captcha
        $captchaMode = CoreMain::getInstance()->getConfigs()->getCaptchaMode();
        $captchaMode = ($captchaMode === 'off' || $captchaMode === 'auto' || $captchaMode === 'manu') ? $captchaMode : 'auto';

        $sessionData = CoreSession::getInstance()->getSessionData();

        // Decide de l'activation
        if ($captchaMode === 'off' || ($captchaMode === 'auto' && $sessionData->hasRank()) || ($captchaMode === 'manu' && $sessionData->hasAdminRank())) {
            $this->enabled = false;
        } else {
            $this->enabled = true;
        }

        if ($this->enabled) {
            $this->object = ($object !== null && is_object($object)) ? $object : null;
        }
    }

    /**
     * Création du captcha.
     * Captcha créé dans l'objet valide sinon retourne en code HTML.
     *
     * @return string le code HTML a incruster dans la page ou une chaîne vide si un objet valide est utilisé
     */
    public function &create(): string
    {
        $rslt = '';

        if ($this->enabled) {
            $this->inputRobotName = ExecCrypt::makeLetterIdentifier($this->randInt(5,
                                                                                   9));
            $mini = (extension_loaded('gd')) ? 0 : 1;
            $mode = $this->randInt($mini,
                                   5);

            $this->createMode($mode);

            if ($this->object !== null) {
                // TODO A vérifier
                if ($this->object instanceOf LibForm) {
                    $this->object->addInputText('cles',
                                                $this->question,
                                                '',
                                                '',
                                                'input captcha');
                    $this->object->addInputHidden($this->inputRobotName,
                                                  '');
                }
            } else {
                $rslt = $this->question . ' <input name="cles" type="text" value="" />'
                    . '<input name="' . $this->inputRobotName . '" type="hidden" value="" />';
            }
        }

        ExecCookie::createCookie('captcha',
                                 ExecString::addSlashes(serialize($this)));
        return $rslt;
    }

    /**
     * Vérifie la validité du captcha.
     *
     * @param LibCaptcha $object
     * @return bool
     */
    public static function &check($object = null): bool
    {
        $rslt = false;

        if ($object === null || !is_object($object)) {
            $object = unserialize(stripslashes(ExecCookie::getCookie('captcha')));
        }

        if (is_object($object)) {
            $rslt = $object->internalCheck();
        }

        if (!$rslt) {
            CoreLogger::addUserWarning(CAPTCHA_INVALID);
        }
        return $rslt;
    }

    /**
     * Création du captcha en fonction du mode.
     *
     * @param int $mode
     */
    private function createMode(int &$mode): void
    {
        switch ($mode) {
            case 0:
                $this->makePicture();
                break;
            case 1:
                $this->makeSimpleCalculation();
                break;
            case 2:
                $this->makeNumberMonth();
                break;
            case 3:
                $this->makeLetter();
                break;
            case 4:
                $this->makeLetters();
                break;
            case 5:
                $this->makeNumbers();
                break;
            default:
                $this->makeLetter();
                break;
        }
    }

    /**
     * Vérifie la validité du captcha.
     *
     * @return bool
     */
    private function &internalCheck(): bool
    {
        $rslt = false;

        $code = CoreRequest::getString('cles',
                                       '',
                                       CoreRequestType::POST);
        $inputRobot = CoreRequest::getString($this->inputRobotName,
                                             '',
                                             CoreRequestType::POST);

        // Vérification du formulaire
        if (empty($inputRobot) && $code === $this->response) {
            $rslt = true;
        } else {
            // TODO A VERIFIER
            CoreLogger::addUserWarning(CAPTCHA_INVALID);
        }
        return $rslt;
    }

    /**
     * Retourne un valeur aléatoire.
     *
     * @param int $mini
     * @param int $max
     * @return int
     */
    private function randInt(int $mini,
                             int $max): int
    {
        if (!self::$iniRand) {
            self::initRand();
        }
        return mt_rand($mini,
                       $max);
    }

    /**
     * Créé un calcul simple.
     */
    private function makeSimpleCalculation(): void
    {
        $numberOne = $this->randInt(0,
                                    9);
        $numberTwo = $this->randInt(1,
                                    12);

        // Choix de l'operateur de façon aléatoire
        $operateur = ($numberTwo >= $numberOne) ? array(
            '+',
            '*') : array(
            '-',
            '+',
            '*');
        $operateur = $operateur[array_rand($operateur)];

        // Calcul de la réponse
        eval('$this->response = strval(' . $numberOne . $operateur . $numberTwo . ');');

        // Affichage aléatoire de l'opérateur
        if ($this->randInt(0,
                           1) == 1) {
            // Affichage de l'opérateur en lettre
            switch ($operateur) {
                case '*':
                    $operateur = 'fois';
                    break;
                case '-':
                    $operateur = 'moins';
                    break;
                case '+':
                    $operateur = 'plus';
                    break;
                default:
                    $operateur = 'plus';
                    break;
            }
        } else {
            // Affichage de l'opérateur en symbole
            $operateur = ($operateur === '*' && $this->randInt(0,
                                                               1) == 1) ? 'x' : $operateur;
        }

        $this->question = CAPTCHA_MAKE_SIMPLE_CALCULATION . ' ' . $numberOne . ' ' . $operateur . ' ' . $numberTwo . ' ?';
    }

    /**
     * Ecrire un certain nombre de lettre de l'alphabet.
     */
    private function makeLetters(): void
    {
        $number = $this->randInt(1,
                                 6);

        $this->question = CAPTCHA_MAKE_LETTERS . ' ' . $number;
        $this->response = substr('abcdef',
                                 0,
                                 $number);
    }

    /**
     * Ecrire la lettre de l'alphabet correspondant au chiffre.
     */
    private function makeLetter(): void
    {
        $number = $this->randInt(1,
                                 6);

        $this->question = CAPTCHA_MAKE_LETTER . ' ' . $number;
        $this->response = substr('abcdef',
                                 $number - 1,
                                 1);
    }

    /**
     * Ecrire un certain nombre de chiffre.
     */
    private function makeNumbers(): void
    {
        $number = $this->randInt(1,
                                 6);

        $this->question = CAPTCHA_MAKE_NUMBERS . ' ' . $number;
        $this->response = substr('012345',
                                 0,
                                 $number + 1);
    }

    /**
     * Convertir en lettre un mois demandé en chiffre et inversement.
     */
    private function makeNumberMonth(): void
    {
        $number = $this->randInt(1,
                                 12);

        // Recherche du mois par rapport au chiffre
        switch ($number) {
            case 1:
                $month = JANUARY;
                break;
            case 2:
                $month = FEBRUARY;
                break;
            case 3:
                $month = MARCH;
                break;
            case 4:
                $month = APRIL;
                break;
            case 5:
                $month = MAY;
                break;
            case 6:
                $month = JUNE;
                break;
            case 7:
                $month = JULY;
                break;
            case 8:
                $month = AUGUST;
                break;
            case 9:
                $month = SEPTEMBER;
                break;
            case 10:
                $month = OCTOBER;
                break;
            case 11:
                $month = NOVEMBER;
                break;
            case 12:
                $month = DECEMBER;
                break;
            default:
                $month = JANUARY;
                break;
        }

        if ($this->randInt(0,
                           1) == 0) {
            // Ecrire en lettre un mois de l'année
            $this->question = CAPTCHA_MAKE_NUMBER_TO_MONTH . ' ' . $number . ' ?';
            $this->response = $month;
        } else {
            // Ecrire en chiffre un mois de l'année
            $this->question = CAPTCHA_MAKE_MONTH_TO_NUMBER . ' ' . $month . ' ?';
            $this->response = $number;
        }
    }

    /**
     * Génére une image.
     */
    private function makePicture(): void
    {// TODO a vérifier
        $this->response = ExecCrypt::makeIdentifier($this->randInt(3,
                                                                   6));
        $this->question = CAPTCHA_MAKE_PICTURE_CODE . ': ' . '<img src="index.php?' . CoreLayout::REQUEST_LAYOUT . '=' . CoreLayout::BLOCK . '&amp;' . CoreLayout::REQUEST_BLOCKTYPE . '=ImageGenerator&amp;mode=code&amp;code=' . $this->response . '" alt="" />\n';
    }

    /**
     * Initialise le compteur de donnée aléatoire.
     */
    private static function initRand(): void
    {
        mt_srand((double) microtime() * 1000000);
        self::$iniRand = true;
    }
}
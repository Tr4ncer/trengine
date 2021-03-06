<?php

namespace PassionEngine\Engine\Core;

use PassionEngine\Engine\Exec\ExecString;

/**
 * Gestionnaire de traduction de texte.
 *
 * @author Sébastien Villemain
 */
class CoreTranslate
{

    /**
     * Liste des différentes langues.
     *
     * @var array
     */
    private const LANGUAGE_LIST = array(
        'aa' => 'Afar',
        'ab' => 'Abkhazian',
        'af' => 'Afrikaans',
        'am' => 'Amharic',
        'ar' => 'Arabic',
        'as' => 'Assamese',
        'ae' => 'Avestan',
        'ay' => 'Aymara',
        'az' => 'Azerbaijani',
        'ba' => 'Bashkir',
        'be' => 'Belarusian',
        'bn' => 'Bengali',
        'bh' => 'Bihari',
        'bi' => 'Bislama',
        'bo' => 'Tibetan',
        'bs' => 'Bosnian',
        'br' => 'Breton',
        'bg' => 'Bulgarian',
        'ca' => 'Catalan',
        'cs' => 'Czech',
        'ch' => 'Chamorro',
        'ce' => 'Chechen',
        'cn' => 'ChineseSimp',
        'cv' => 'Chuvash',
        'kw' => 'Cornish',
        'co' => 'Corsican',
        'cy' => 'Welsh',
        'da' => 'Danish',
        'de' => 'German',
        'dz' => 'Dzongkha',
        'el' => 'Greek',
        'en' => 'English',
        'eo' => 'Esperanto',
        'et' => 'Estonian',
        'eu' => 'Basque',
        'fo' => 'Faroese',
        'fa' => 'Persian',
        'fj' => 'Fijian',
        'fi' => 'Finnish',
        'fr' => 'French',
        'fy' => 'Frisian',
        'gd' => 'Gaelic',
        'ga' => 'Irish',
        'gl' => 'Gallegan',
        'gv' => 'Manx',
        'gn' => 'Guarani',
        'gu' => 'Gujarati',
        'ha' => 'Hausa',
        'he' => 'Hebrew',
        'hz' => 'Herero',
        'hi' => 'Hindi',
        'ho' => 'Hiri Motu',
        'hr' => 'Croatian',
        'hu' => 'Hungarian',
        'hy' => 'Armenian',
        'iu' => 'Inuktitut',
        'ie' => 'Interlingue',
        'id' => 'Indonesian',
        'ik' => 'Inupiaq',
        'is' => 'Icelandic',
        'it' => 'Italian',
        'jw' => 'Javanese',
        'ja' => 'Japanese',
        'kl' => 'Kalaallisut',
        'kn' => 'Kannada',
        'ks' => 'Kashmiri',
        'ka' => 'Georgian',
        'kk' => 'Kazakh',
        'km' => 'Khmer',
        'ki' => 'Kikuyu',
        'rw' => 'Kinyarwanda',
        'ky' => 'Kirghiz',
        'kv' => 'Komi',
        'ko' => 'Korean',
        'ku' => 'Kurdish',
        'lo' => 'Lao',
        'la' => 'Latin',
        'lv' => 'Latvian',
        'ln' => 'Lingala',
        'lt' => 'Lithuanian',
        'lb' => 'Letzeburgesch',
        'mh' => 'Marshall',
        'ml' => 'Malayalam',
        'mr' => 'Marathi',
        'mk' => 'Macedonian',
        'mg' => 'Malagasy',
        'mt' => 'Maltese',
        'mo' => 'Moldavian',
        'mn' => 'Mongolian',
        'mi' => 'Maori',
        'ms' => 'Malay',
        'my' => 'Burmese',
        'na' => 'Nauru',
        'nv' => 'Navajo',
        'ng' => 'Ndonga',
        'ne' => 'Nepali',
        'nl' => 'Dutch',
        'nb' => 'Norwegian',
        'ny' => 'Chichewa',
        'or' => 'Oriya',
        'om' => 'Oromo',
        'pa' => 'Panjabi',
        'pi' => 'Pali',
        'pl' => 'Polish',
        'pt' => 'Portuguese',
        'ps' => 'Pushto',
        'qu' => 'Quechua',
        'ro' => 'Romanian',
        'rn' => 'Rundi',
        'ru' => 'Russian',
        'sg' => 'Sango',
        'sa' => 'Sanskrit',
        'si' => 'Sinhalese',
        'sk' => 'Slovak',
        'sl' => 'Slovenian',
        'sm' => 'Samoan',
        'sn' => 'Shona',
        'sd' => 'Sindhi',
        'so' => 'Somali',
        'es' => 'Spanish',
        'sq' => 'Albanian',
        'sc' => 'Sardinian',
        'sr' => 'Serbian',
        'ss' => 'Swati',
        'su' => 'Sundanese',
        'sw' => 'Swahili',
        'sv' => 'Swedish',
        'ty' => 'Tahitian',
        'ta' => 'Tamil',
        'tt' => 'Tatar',
        'te' => 'Telugu',
        'tg' => 'Tajik',
        'tl' => 'Tagalog',
        'th' => 'Thai',
        'ti' => 'Tigrinya',
        'tn' => 'Tswana',
        'ts' => 'Tsonga',
        'tk' => 'Turkmen',
        'tr' => 'Turkish',
        'tw' => 'ChineseTrad',
        'ug' => 'Uighur',
        'uk' => 'Ukrainian',
        'ur' => 'Urdu',
        'uz' => 'Uzbek',
        'vi' => 'Vietnamese',
        'wo' => 'Wolof',
        'xh' => 'Xhosa',
        'yi' => 'Yiddish',
        'yo' => 'Yoruba',
        'za' => 'Zhuang',
        'zh' => 'Chinese',
        'zu' => 'Zulu'
    );

    /**
     * Instance du gestionnaire du traducteur.
     *
     * @var CoreTranslate
     */
    private static $coreTranslate = null;

    /**
     * Informations sur la langue utilisée.
     *
     * @var array
     */
    private $languageInfos = array();

    /**
     * Mémorise temporairement les données de traduction.
     *
     * @var array
     */
    private $cache = array();

    /**
     * Mémorise les fichiers traduit.
     *
     * @var array
     */
    private $translated = array();

    /**
     * Nouveau gestionnaire.
     */
    private function __construct()
    {
        $this->languageInfos['extension'] = self::getLanguageExtension();
        $this->languageInfos['name'] = $this->getLanguageTranslated();

        $this->configureLocale();
    }

    /**
     * Retourne le gestionnaire de traduction.
     *
     * @return CoreTranslate
     */
    public static function &getInstance(): CoreTranslate
    {
        self::checkInstance();
        return self::$coreTranslate;
    }

    /**
     * Vérification de l'instance du gestionnaire de traduction.
     */
    public static function checkInstance(): void
    {
        if (self::$coreTranslate === null) {
            self::$coreTranslate = new CoreTranslate();
            self::$coreTranslate->translate(CoreLoader::ENGINE_SUBTYPE);
        }
    }

    /**
     * Ajout d'un tableau de traduction pour utilisation prochaine.
     *
     * @param array $cache
     */
    public function affectCache(array $cache): void
    {
        if (!empty($cache) && empty($this->cache)) {
            $this->cache = $cache;
        }
    }

    /**
     * Retourne la langue courante.
     *
     * @return string
     */
    public function &getCurrentLanguage(): string
    {
        return $this->languageInfos['name'];
    }

    /**
     * Retourne l'extension de la langue courante.
     *
     * @return string
     */
    public function &getCurrentLanguageExtension(): string
    {
        return $this->languageInfos['extension'];
    }

    /**
     * Traduction de la page via le fichier.
     *
     * @param string $rootDirectoryPath Chemin racine contenant le dossier de traduction.
     */
    public function translate(string $rootDirectoryPath): void
    {
        if (!empty($rootDirectoryPath) && !$this->translated($rootDirectoryPath)) {
            $this->fireTranslation($rootDirectoryPath);
            $this->setTranslated($rootDirectoryPath);
        }
    }

    /**
     * Retourne un tableau contenant les langues disponibles.
     *
     * @return array
     */
    public static function &getLangList(): array
    {
        return CoreCache::getInstance()->getFileList(CoreLoader::ENGINE_SUBTYPE . DIRECTORY_SEPARATOR . CoreLoader::TRANSLATE_FILE,
                                                     '.' . CoreLoader::TRANSLATE_EXTENSION);
    }

    /**
     * Suppression du cache de traduction.
     *
     * @param string $rootDirectoryPath Chemin racine contenant le dossier de traduction.
     */
    public static function removeCache(string $rootDirectoryPath = ''): void
    {
        $coreCache = CoreCache::getInstance(CoreCacheSection::TRANSLATE);
        $langCacheFileName = self::getLangCachePrefixFileName($rootDirectoryPath);
        $langues = self::getLangList();

        foreach ($langues as $langue) {
            $coreCache->removeCache($langCacheFileName . $langue . '.' . CoreLoader::TRANSLATE_EXTENSION . '.php');
        }
    }

    /**
     * Retourne la description du code d'erreur (une constante).
     *
     * @param string $constantName Nom de la constante.
     * @param array $args Tableau de valeur.
     * @return string Description du code d'erreur.
     */
    public static function &getConstantDescription(string $constantName,
                                                   array $args = null): string
    {
        $rslt = '';

        if (!empty($constantName) && defined($constantName)) {
            $rslt = constant($constantName);
        }

        if (!empty($rslt) && $args !== null) {
            $rslt = vsprintf($rslt,
                             $args);
        }
        return $rslt;
    }

    /**
     * Détermine si la traduction a déjà été appliquée.
     *
     * @param string $rootDirectoryPath Chemin racine contenant le dossier de traduction.
     * @return bool
     */
    private function translated(string $rootDirectoryPath): bool
    {
        return isset($this->translated[$rootDirectoryPath]);
    }

    /**
     * Signale que la traduction a déjà été appliquée.
     *
     * @param string $rootDirectoryPath Chemin racine contenant le dossier de traduction.
     */
    private function setTranslated(string $rootDirectoryPath): void
    {
        $this->translated[$rootDirectoryPath] = true;
    }

    /**
     * Procédure de traduction.
     *
     * @param string $rootDirectoryPath Chemin racine contenant le dossier de traduction.
     */
    private function fireTranslation(string $rootDirectoryPath): void
    {
        if (!$this->translateWithCache($rootDirectoryPath)) {
            $content = $this->getTranslation($rootDirectoryPath);

            if (!empty($content)) {
                $this->createTranslationCache($rootDirectoryPath,
                                              $content);
                $this->translateWithBuffer($content);
            }
        }
    }

    /**
     * Traduction via le cache.
     *
     * @param string $rootDirectoryPath Chemin racine contenant le dossier de traduction.
     * @return bool
     */
    private function translateWithCache(string $rootDirectoryPath): bool
    {
        $translated = false;

        if (CoreLoader::isCallable('CoreCache')) {
            $coreCache = CoreCache::getInstance(CoreCacheSection::TRANSLATE);
            $langCacheFileName = $this->getLangCacheFileName($rootDirectoryPath);

            if ($coreCache->cached($langCacheFileName)) {
                $langOriginalPath = CoreLoader::getTranslateAbsolutePath($rootDirectoryPath);

                if ($coreCache->getCacheMTime($langCacheFileName) >= filemtime($langOriginalPath)) {
                    $coreCache->readCache($langCacheFileName);
                    $translated = true;
                }
            }
        }
        return $translated;
    }

    /**
     * Retourne le nom du fichier cache pour la traduction.
     *
     * @param string $rootDirectoryPath Chemin racine contenant le dossier de traduction.
     * @return string
     */
    private function getLangCacheFileName(string $rootDirectoryPath): string
    {
        return self::getLangCachePrefixFileName($rootDirectoryPath) . $this->getCurrentLanguage() . '.php';
    }

    /**
     * Retourne les données de traduction.
     *
     * @param string $rootDirectoryPath Chemin racine contenant le dossier de traduction.
     * @return string
     */
    private function getTranslation(string $rootDirectoryPath): string
    {
        $content = '';

        // Initialisation de la variable de cache
        $this->cache = array();

        if (CoreLoader::translateLoader($rootDirectoryPath) && !empty($this->cache)) {
            $content = $this->serializeTranslation();

            // Vide la variable de cache après utilisation
            $this->cache = array();
        }
        return $content;
    }

    /**
     * Retourne les données de traduction pour mise en cache.
     *
     * @return string
     */
    private function serializeTranslation(): string
    {
        $content = '';

        foreach ($this->cache as $key => $value) {
            if (!empty($key) && !empty($value)) {
                $content .= 'define(\'' . $key . '\',\'' . self::entitiesTranslate($value) . '\');';
            }
        }
        return $content;
    }

    /**
     * Création du cache de traduction.
     *
     * @param string $rootDirectoryPath Chemin racine contenant le dossier de traduction.
     * @param string $content
     */
    private function createTranslationCache(string $rootDirectoryPath,
                                            string $content): void
    {
        if (CoreLoader::isCallable('CoreCache')) {
            $langCacheFileName = $this->getLangCacheFileName($rootDirectoryPath);
            CoreCache::getInstance(CoreCacheSection::TRANSLATE)->writeCacheAsString($langCacheFileName,
                                                                                    $content);
        }
    }

    /**
     * Traduction via mémoire tampon et évaluation à la volée.
     *
     * @param string $content
     */
    private function translateWithBuffer(string $content): void
    {
        eval($content);
    }

    /**
     * Retourne le nom du fichier cache de langue.
     *
     * @param string $rootDirectoryPath Chemin racine contenant le dossier de traduction.
     * @return string
     */
    private static function getLangCachePrefixFileName(string $rootDirectoryPath = ''): string
    {
        if (!empty($rootDirectoryPath) && substr($rootDirectoryPath,
                                                 -1) !== DIRECTORY_SEPARATOR) {
            $rootDirectoryPath .= DIRECTORY_SEPARATOR;
        }
        return str_replace(DIRECTORY_SEPARATOR,
                           '_',
                           $rootDirectoryPath) . CoreLoader::TRANSLATE_EXTENSION . '_';
    }

    /**
     * Recherche et retourne l'extension de la langue.
     *
     * @return string
     */
    private static function &getLanguageExtension(): string
    {
        $validExtension = '';

        // Recherche de la langue du client
        $acceptedLanguages = explode(',',
                                     CoreRequest::getString('HTTP_ACCEPT_LANGUAGE',
                                                            '',
                                                            CoreRequestType::SERVER));
        $extension = strtolower(substr(trim($acceptedLanguages[0]),
                                            0,
                                            2));

        if (self::canUseExtension($extension)) {
            $validExtension = $extension;
        } else {
            $validExtension = $this->getLanguageExtensionFromUrl();
        }
        return $validExtension;
    }

    /**
     * Recherche et retourne l'extension de la langue.
     *
     * @return string
     */
    private static function &getLanguageExtensionFromUrl(): string
    {
        // Recherche de l'URL
        if (!defined('PASSION_ENGINE_URL')) {
            $url = CoreRequest::getString('SERVER_NAME',
                                          '',
                                          CoreRequestType::SERVER);
        } else {
            $url = PASSION_ENGINE_URL;
        }

        // Recherche de l'extension de URL
        $matches = array();
        preg_match('@^(?:http://)?([^/]+)@i',
                   $url,
                   $matches);
        preg_match('/[^.]+\.[^.]+$/',
                   $matches[1],
                   $matches);
        preg_match('/[^.]+$/',
                   $matches[0],
                   $matches);

        $validExtension = '';
        $extension = $matches[0];

        if (self::canUseExtension($extension)) {
            $validExtension = $extension;
        }
        return $validExtension;
    }

    /**
     * Formate l'heure locale.
     */
    private function configureLocale(): void
    {
        if ($this->getCurrentLanguage() === 'french' && PASSION_ENGINE_PHP_OS === 'WIN') {
            setlocale(LC_TIME,
                      'french');
        } else if ($this->getCurrentLanguage() === 'french' && PASSION_ENGINE_PHP_OS === 'BSD') {
            setlocale(LC_TIME,
                      'fr_FR.ISO8859-1');
        } else if ($this->getCurrentLanguage() === 'french') {
            setlocale(LC_TIME,
                      'fr_FR');
        } else {
            // Tentative de formatage via le nom de la langue
            if (!setlocale(LC_TIME,
                           $this->getCurrentLanguage())) {
                // Dernière tentative de formatage sous forme 'fr_FR'
                setlocale(LC_TIME,
                          strtolower($this->getCurrentLanguageExtension()) . '_' . strtoupper($this->getCurrentLanguageExtension()));
            }
        }
    }

    /**
     * Conversion des caractères spéciaux en entitiées UTF-8.
     * Ajout du caractère d'échappement pour utilisation dans le cache.
     *
     * @param string
     * @return string
     */
    private static function &entitiesTranslate(string $text): string
    {
        $text = ExecString::entitiesUtf8($text);
        $text = ExecString::addSlashes($text);
        return $text;
    }

    /**
     * Détermine si l'extension de langue est connue.
     *
     * @param string $extension
     * @return bool
     */
    private static function canUseExtension(string $extension): bool
    {
        return !empty($extension) && isset(self::LANGUAGE_LIST[$extension]);
    }

    /**
     * Retourne la langue la plus appropriée.
     *
     * @return string
     */
    private function &getLanguageTranslated(): string
    {
        $language = self::getLanguageBySession();

        if (empty($language)) {
            $language = self::getLanguageByExtension($this->getCurrentLanguageExtension());

            if (empty($language)) {
                $language = self::getLanguageByDefault();
            }
        }
        return $language;
    }

    /**
     * Retourne la langue du client (via le cookie de session).
     *
     * @return string
     */
    private static function &getLanguageBySession(): string
    {
        $language = '';

        if (CoreLoader::isCallable('CoreSession')) {
            $language = strtolower(trim(CoreSession::getInstance()->getSessionData()->getLangue()));

            if (!self::canUseLanguage($language)) {
                $language = '';
            }
        }
        return $language;
    }

    /**
     * Retourne la langue trouvée via l'extension.
     *
     * @param string $extension
     * @return string
     */
    private static function &getLanguageByExtension(string $extension): string
    {
        $language = strtolower(trim(self::LANGUAGE_LIST[$extension]));

        if (!self::canUseLanguage($language)) {
            $language = '';
        }
        return $language;
    }

    /**
     * Retourne la langue par défaut.
     *
     * @return string
     */
    private static function &getLanguageByDefault(): string
    {
        $language = '';

        if (CoreLoader::isCallable('CoreMain')) {
            // Utilisation de la langue par défaut du site
            $language = CoreMain::getInstance()->getConfigs()->getDefaultLanguage();
        }

        // Malheureusement la langue par défaut est aussi invalide
        if (empty($language) || !self::canUseLanguage($language)) {
            $language = 'english';
        }
        return $language;
    }

    /**
     * Détermine si la langue est disponible.
     *
     * @param string $language
     * @return bool Langue disponible.
     */
    private static function canUseLanguage(string $language): bool
    {
        return !empty($language) && is_file(PASSION_ENGINE_ROOT_DIRECTORY . DIRECTORY_SEPARATOR . CoreLoader::getFilePathFromTranslate(CoreLoader::ENGINE_SUBTYPE,
                                                                                                                                       $language) . '.php');
    }
}
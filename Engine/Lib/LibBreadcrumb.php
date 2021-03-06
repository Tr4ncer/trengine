<?php

namespace PassionEngine\Engine\Lib;

use PassionEngine\Engine\Core\CoreMain;
use PassionEngine\Engine\Core\CoreHtml;

/**
 * Gestionnaire du fil d'Ariane.
 *
 * @author Sébastien Villemain
 */
class LibBreadcrumb
{

    /**
     * Instance de la classe.
     *
     * @var LibBreadcrumb
     */
    private static $libBreadcrumb = null;

    /**
     * Le fil d'Ariane.
     *
     * @var array
     */
    private $breadcrumbTrail = array();

    private function __construct()
    {
        // Ajoute la page principal
        $this->addTrail(CoreMain::getInstance()->getConfigs()->getDefaultSiteName(),
                        'index.php');
    }

    /**
     * Retourne l'instance LibBreadcrumb.
     *
     * @return LibBreadcrumb
     */
    public static function &getInstance(): LibBreadcrumb
    {
        if (self::$libBreadcrumb === null) {
            self::$libBreadcrumb = new LibBreadcrumb();
        }
        return self::$libBreadcrumb;
    }

    /**
     * Ajoute un tracé au fil d'Ariane.
     *
     * @param string $trail
     * @param string $link
     */
    public function addTrail(string $trail,
                             string $link = ''): void
    {
        if (!empty($trail)) {
            $trail = self::getTrailDescription($trail);

            if (!empty($link)) {
                $trail = CoreHtml::getLink($link,
                                           $trail);
            }

            $this->breadcrumbTrail[] = $trail;
        }
    }

    /**
     * Retourne le fil d'Ariane complet.
     *
     * @return string
     */
    public function &getBreadcrumbTrail(): string
    {
        $rslt = '<nav class="breadcrumbtrail"><ul>';

        foreach ($this->breadcrumbTrail as $trail) {
            $rslt .= '<li>' . $trail . '</li>';
        }
        $rslt .= '</ul></nav>';
        return $rslt;
    }

    private static function &getTrailDescription(string $trail): string
    {
        $constant = 'TRAIL_' . strtoupper($trail);

        if (defined($constant)) {
            $trail = constant($constant);
        }
        return $trail;
    }
}
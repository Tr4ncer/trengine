<?php

namespace PassionEngine\Engine\Lib;

/**
 * Gestionnaire de tableau automatique.
 *
 * @link https://developer.mozilla.org/docs/Web/HTML/Element/table
 * @author Sébastien Villemain
 */
class LibRack
{

    /**
     * Première ligne définissant les colonnes.
     *
     * @var array
     */
    private $firstLine = array();

    /**
     * Lignes de données du tableau.
     *
     * @var array
     */
    private $lines = array();

    /**
     * Nouveau tableau.
     *
     * @param array $startLine array('size in %', 'my title')
     */
    public function __construct(array &$startLine = array())
    {
        $this->firstLine = $startLine;
    }

    /**
     * Ajoute une colonne.
     *
     * @param int $size taille en pourcentage.
     * @param string $title titre de la colonne.
     */
    public function addColumn($size,
                              $title = ''): void
    {
        $this->firstLine[] = array(
            $size,
            $title);
    }

    /**
     * Ajoute une ligne au tableau.
     *
     * @param array $line tableau contenant dans l'ordre toutes les colonnes de la ligne.
     */
    public function addLine(array $line): void
    {
        $this->lines[] = $line;
    }

    /**
     * Retourne le rendu du rack complet.
     *
     * @param string $class
     * @return string
     */
    public function &render(string $class = ''): string
    {
        $content = '<table class="' . ((!empty($class)) ? $class : 'table') . '">'
            . '<tbody><tr class="first">';

        // Création de la 1ère lignes
        foreach ($this->firstLine as $column) {
            $content .= '<td style="width: ' . $column[0] . '%;">' . $column[1] . '</td>';
        }

        $content .= '</tr>';

        // Création de toutes les lignes
        foreach ($this->lines as $line) {
            $content .= '<tr>';

            foreach ($line as $column) {
                $content .= '<td>' . $column . '</td>';
            }

            $content .= '</tr>';
        }

        $content .= '</tbody></table>';
        return $content;
    }
}
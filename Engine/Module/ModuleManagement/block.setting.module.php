<?php

class Module_Management_Block extends ModuleModel
{

    public function setting()
    {
        $localView = CoreRequest::getWord("localView");

        // Affichage et traitement
        $content = "";
        switch ($localView) {
            case "sendMoveUp":
                $this->sendMoveUp();
                $content .= $this->tabHome();
                break;
            case "sendMoveDown":
                $this->sendMoveDown();
                $content .= $this->tabHome();
                break;
            case "sendDelete":
                $this->sendDelete();
                $content .= $this->tabHome();
                break;
            case "sendCopy":
                $this->sendCopy();
                $content .= $this->tabEdit(CoreSql::getInstance()->insertId());
                break;
            case "tabEdit":
                $content .= $this->tabEdit();
                break;
            case "tabAdd":
                $content .= $this->tabAdd();
                break;
            default:
                $content .= $this->tabHome();
        }

        if (CoreMain::getInstance()->getRoute()->isDefaultLayout()) {
            return "<div id=\"block_main_setting\">"
                    . $content . "</div>";
        }
        return $content;
    }

    private function tabHome()
    {
        $firstLine = array(
            array(
                35,
                BLOCK_TITLE),
            array(
                20,
                BLOCK_TYPE),
            array(
                10,
                BLOCK_SIDE),
            array(
                5,
                BLOCK_POSITION),
            array(
                10,
                BLOCK_ACCESS),
            array(
                20,
                BLOCK_VIEW_MODULE_PAGE)
        );
        $rack = new LibRack($firstLine);

        CoreSql::getInstance()->select(
                CoreTable::BLOCKS_TABLE,
                array(
                    "block_id",
                    "side",
                    "position",
                    "title",
                    "type",
                    "rank",
                    "all_modules"),
                array(),
                array(
                    "position")
        );
        if (CoreSql::getInstance()->affectedRows() > 0) {
            $rslt = CoreSql::getInstance()->fetchArray();

            foreach ($rslt as $row) {
                // Parametre de la ligne
                $title = CoreHtml::getLink("?" . CoreLayout::REQUEST_MODULE . "=management&manage=block&localView=tabEdit&blockId=" . $row['block_id'],
                                           $row['title']);
                $type = $row['type'];
                $side = LibBlock::getSideNumericDescription($row['side']);
                $position = CoreHtml::getLinkWithAjax("?" . CoreLayout::REQUEST_MODULE . "=management&manage=block&localView=sendMoveUp&blockId=" . $row['block_id'],
                                                      "?" . CoreLayout::REQUEST_MODULE . "=management&manage=block&localView=sendMoveUp&blockId=" . $row['block_id'],
                                                      "#block_main_setting",
                                                      "^"
                );
                $position .= $row['position'];
                $position .= CoreHtml::getLinkWithAjax("?" . CoreLayout::REQUEST_MODULE . "=management&manage=block&localView=sendMoveDown&blockId=" . $row['block_id'],
                                                       "?" . CoreLayout::REQUEST_MODULE . "=management&manage=block&localView=sendMoveDown&blockId=" . $row['block_id'],
                                                       "#block_main_setting",
                                                       "v"
                );
                $rank = CoreAccess::getRankAsLitteral($row['rank']);
                $allModules = ($row['all_modules'] === 1) ? BLOCK_ALL_PAGE : BLOCK_VARIES_PAGE;
                // Ajout de la ligne au tableau
                $rack->addLine(array(
                    $title,
                    $type,
                    $side,
                    $position,
                    $rank,
                    $allModules));
            }
        }

        Module_Management_Index::addAddButtonInToolbar("localView=tabAdd");
        return $rack->render();
    }

    private function sendMoveUp()
    {
        $blockId = CoreRequest::getInteger(CoreLayout::REQUEST_BLOCKID,
                                           -1);

        if ($blockId > -1) { // Si l'id semble valide
            CoreSql::getInstance()->select(
                    CoreTable::BLOCKS_TABLE,
                    array(
                        "side",
                        "position"),
                    array(
                        "block_id = '" . $blockId . "'")
            );
            if (CoreSql::getInstance()->affectedRows() > 0) { // Si le block existe
                $blockMove = CoreSql::getInstance()->fetchArray(); // Récuperation des informations sur le block

                if ($blockMove['position'] > 0) {
                    // Requête de Sélection des autres blocks
                    CoreSql::getInstance()->select(
                            CoreTable::BLOCKS_TABLE,
                            array(
                                "block_id",
                                "position"),
                            array(
                                "side = '" . $blockMove['side'] . "' AND",
                                "(position = '" . $blockMove['position'] . "' OR position = '"
                                . ($blockMove['position'] - 1) . "')")
                    );
                    if (CoreSql::getInstance()->affectedRows() > 0) {
                        CoreSql::getInstance()->addArrayBuffer("blockMoveUp");
                        // Mise à jour de position
                        while ($row = CoreSql::getInstance()->fetchBuffer("blockMoveUp")) {
                            $row['position'] = ($row['block_id'] == $blockId) ? $row['position'] - 1 : $row['position'] + 1;

                            CoreSql::getInstance()->update(
                                    CoreTable::BLOCKS_TABLE,
                                    array(
                                        "position" => $row['position']),
                                    array(
                                        "block_id = '" . $row['block_id'] . "'")
                            );
                        }

                        CoreSql::getInstance()->getSelectedBase()->freeBuffer();
                        CoreLogger::addInformationMessage(DATA_SAVED);
                    }
                }
            } else {
                CoreLogger::addInformationMessage(DATA_INVALID);
            }
        } else {
            CoreLogger::addInformationMessage(DATA_INVALID);
        }
    }

    private function sendMoveDown()
    {
        $blockId = CoreRequest::getInteger(CoreLayout::REQUEST_BLOCKID,
                                           -1);

        if ($blockId > -1) { // Si l'id semble valide
            CoreSql::getInstance()->select(
                    CoreTable::BLOCKS_TABLE,
                    array(
                        "side",
                        "position"),
                    array(
                        "block_id = '" . $blockId . "'")
            );
            if (CoreSql::getInstance()->affectedRows() > 0) { // Si le block existe
                $blockMove = CoreSql::getInstance()->fetchArray(); // Récuperation des informations sur le block
                // Sélection du block le plus bas
                CoreSql::getInstance()->select(
                        CoreTable::BLOCKS_TABLE,
                        array(
                            "block_id",
                            "position"),
                        array(
                            "side = '" . $blockMove['side'] . "'"),
                        array(
                            "position DESC"),
                        "1"
                );

                if (CoreSql::getInstance()->affectedRows() > 0) {
                    $blockDown = CoreSql::getInstance()->fetchArray();

                    if ($blockMove['position'] < $blockDown['position']) {
                        // Requête de Sélection des autres blocks
                        CoreSql::getInstance()->select(
                                CoreTable::BLOCKS_TABLE,
                                array(
                                    "block_id",
                                    "position"),
                                array(
                                    "side = '" . $blockMove['side'] . "' AND",
                                    "(position = '" . $blockMove['position'] . "' OR position = '"
                                    . ($blockMove['position'] + 1) . "')")
                        );
                        if (CoreSql::getInstance()->affectedRows() > 0) {
                            CoreSql::getInstance()->addArrayBuffer("blockMoveDown");
                            // Mise à jour de position
                            while ($row = CoreSql::getInstance()->fetchBuffer("blockMoveDown")) {
                                $row['position'] = ($row['block_id'] == $blockId) ? $row['position'] + 1 : $row['position'] - 1;

                                CoreSql::getInstance()->update(
                                        CoreTable::BLOCKS_TABLE,
                                        array(
                                            "position" => $row['position']),
                                        array(
                                            "block_id = '" . $row['block_id'] . "'")
                                );
                            }
                            CoreSql::getInstance()->getSelectedBase()->freeBuffer();
                            CoreLogger::addInformationMessage(DATA_SAVED);
                        }
                    }
                }
            } else {
                CoreLogger::addInformationMessage(DATA_INVALID);
            }
        } else {
            CoreLogger::addInformationMessage(DATA_INVALID);
        }
    }

    private function tabEdit($blockId = -1)
    {
        if ($blockId < 0) {
            $blockId = CoreRequest::getInteger(CoreLayout::REQUEST_BLOCKID,
                                               -1);
        }

        if ($blockId > -1) { // Si l'id semble valide
            CoreSql::getInstance()->select(
                    CoreTable::BLOCKS_TABLE,
                    array(
                        "side",
                        "position",
                        "title",
                        "type",
                        "rank",
                        "all_modules"),
                    array(
                        "block_id = '" . $blockId . "'")
            );
            // TODO gestion de blocks_visibility +  blocks_configs
            if (CoreSql::getInstance()->affectedRows() > 0) { // Si le block existe
                $block = CoreSql::getInstance()->fetchArray();
                LibBreadcrumb::getInstance()->addTrail($block['title'],
                                                       "?" . CoreLayout::REQUEST_MODULE . "=management&manage=block&localView=tabEdit&blockId=" . $blockId);

                $form = new LibForm("management-block-blockedit");
                $form->setTitle(BLOCK_EDIT_TITLE);
                $form->setDescription(BLOCK_EDIT_DESCRIPTION);
                $form->addSpace();

                $form->addHtmlInFieldset("ID : #" . $blockId);
                $form->addInputText("blockTitle",
                                    BLOCK_TITLE,
                                    $block['title']);

                $blockList = LibBlock::getBlockList();
                $form->addSelectOpenTag(CoreLayout::REQUEST_BLOCKTYPE,
                                        BLOCK_TYPE);
                $form->addSelectItemTag($block['type'],
                                        "",
                                        true);
                foreach ($blockList as $blockType) {
                    if ($blockType == $block['type'])
                        continue;
                    $form->addSelectItemTag($blockType);
                }
                $form->addSelectCloseTag();

                $sideList = LibBlock::getSideList();
                $form->addSelectOpenTag("blockSide",
                                        BLOCK_SIDE);
                $currentSideName = "";
                foreach ($sideList as $blockSide) {
                    if ($blockSide['numeric'] == $block['side']) {
                        $currentSideName = $blockSide['letters'];
                        continue;
                    }
                    $form->addSelectItemTag($blockSide['numeric'],
                                            $blockSide['numeric'] . " " . $blockSide['letters']);
                }
                $form->addSelectItemTag($block['side'],
                                        $block['side'] . " " . $currentSideName,
                                        true);
                $form->addSelectCloseTag();
                // TODO rafraichir la liste des ordres (position) suivant la liste des positions (side)
                $form->addInputText("blockTitle",
                                    BLOCK_POSITION,
                                    $block['position']);

                $rankList = CoreAccess::getRankList();
                $form->addSelectOpenTag("blockRank",
                                        BLOCK_ACCESS);
                $currentRankName = "";
                foreach ($rankList as $blockRank) {
                    if ($blockRank['numeric'] == $block['rank']) {
                        $currentRankName = $blockRank['letters'];
                        continue;
                    }
                    $form->addSelectItemTag($blockRank['numeric'],
                                            $blockRank['numeric'] . " " . $blockRank['letters']);
                }
                $form->addSelectItemTag($block['rank'],
                                        $block['rank'] . " " . $currentRankName,
                                        true);
                $form->addSelectCloseTag();
                // TODO faire une liste cliquable avec un bouton radio "toutes les pages" et "aucune page" (= rank -1)
                $form->addInputText("blockTitle",
                                    BLOCK_VIEW_MODULE_PAGE,
                                    $block['mods']);

                $position .= CoreHtml::getLinkWithAjax("?" . CoreLayout::REQUEST_MODULE . "=management&manage=block&localView=movedown&blockId=" . $row['block_id'],
                                                       "?" . CoreLayout::REQUEST_MODULE . "=management&manage=block&localView=movedown&blockId=" . $row['block_id'],
                                                       "#block_main_setting",
                                                       "v"
                );
                Module_Management_Index::addDeleteButtonInToolbar("localView=sendDelete&blockId=" . $blockId);
                Module_Management_Index::addCopyButtonInToolbar("localView=sendCopy&blockId=" . $blockId);
                Module_Management_Index::addEditButtonInToolbar("localView=tabAdd",
                                                                PREVIEW);
                Module_Management_Index::addAddButtonInToolbar("localView=tabAdd");
                return $form->render();
            } else {
                CoreLogger::addInformationMessage(DATA_INVALID);
            }
        } else {
            CoreLogger::addInformationMessage(DATA_INVALID);
        }
        return "";
    }

    private function sendDelete()
    {
        $blockId = CoreRequest::getInteger(CoreLayout::REQUEST_BLOCKID,
                                           -1);

        if ($blockId > -1) { // Si l'id semble valide
            CoreSql::getInstance()->select(
                    CoreTable::BLOCKS_TABLE,
                    array(
                        "type"),
                    array(
                        "block_id = '" . $blockId . "'")
            );
            if (CoreSql::getInstance()->affectedRows() > 0) { // Si le block existe
                $block = CoreSql::getInstance()->fetchArray();

                $blockClassName = CoreLoader::getFullQualifiedClassName(CoreLoader::BLOCK_FILE . ucfirst($block['type']));
                $loaded = CoreLoader::classLoader($blockClassName);

                if ($loaded) {
                    if (CoreLoader::isCallable($blockClassName,
                                               "uninstall")) {
                        $BlockClass = new $blockClassName();
                        $BlockClass->uninstall();
                    }
                }

                CoreSql::getInstance()->delete(
                        CoreTable::BLOCKS_TABLE,
                        array(
                            "block_id = '" . $blockId . "'")
                );
                CoreTranslate::removeCache("blocks/" . $block['type']);
                CoreLogger::addInformationMessage(DATA_DELETED);
            } else {
                CoreLogger::addInformationMessage(DATA_INVALID);
            }
        } else {
            CoreLogger::addInformationMessage(DATA_INVALID);
        }
    }

    private function sendCopy()
    {
        $blockId = CoreRequest::getInteger("

                blockId",
                                           -1);

        if ($blockId > -1) { // Si l'id semble valide
            $keys = array(
                "side ",
                "position",
                "title",
                "type",
                "rank",
                "all_modules");
            CoreSql::getInstance()->select(
                    CoreTable::BLOCKS_TABLE,
                    $keys,
                    array(
                        "block_id = '" . $blockId . "'")
            );
            if (CoreSql::getInstance()->affectedRows() > 0) { // Si le block existe
                $block = CoreSql::getInstance()->fetchArray();
                $block['title'] = $block['title'] . " Copy";
                CoreSql::getInstance()->insert(
                        CoreTable::BLOCKS_TABLE,
                        $keys,
                        $block
                );
                CoreLogger::addInformationMessage(DATA_COPIED);
            } else {
                CoreLogger::addInformationMessage(DATA_INVALID);
            }
        } else {
            CoreLogger::addInformationMessage(DATA_INVALID);
        }
    }

    private function tabAdd()
    {
        LibBreadcrumb::getInstance()->addTrail(ADD,
                                               "?

                module = management &manage = block&localView = tabAdd

                ");
    }

    private function sendAdd()
    {

    }
}
?>
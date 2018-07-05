<?php

namespace TREngine\Engine\Core;

use TREngine\Engine\Exec\ExecString;
use DateTime;

/**
 * Collecteur d'information sur une session d'un utilisateur.
 *
 * @author Sébastien Villemain
 */
class CoreSessionData extends CoreDataStorage implements CoreAccessToken
{

    /**
     * Nouvelle information de block.
     *
     * @param array $data
     */
    public function __construct(array &$data)
    {
        parent::__construct();

        // Vérification des informations
        if (count($data) < 3) {
            $data = array();
        }

        $this->newStorage($data);
        $this->updateDataValue("name",
                               ExecString::stripSlashes($this->getString("name")));
        $this->updateDataValue("rank",
                               $this->getInt("rank"));
        $this->updateDataValue("signature",
                               ExecString::stripSlashes($this->getString("signature")));
        $this->updateDataValue("website",
                               ExecString::stripSlashes($this->getString("website")));
        $this->updateDataValue("registration_date",
                               new DateTime($this->getString("registration_date")));
    }

    /**
     * Identifiant du client.
     *
     * @return string
     */
    public function &getId(): string
    {
        return $this->getIdAsInt();
    }

    /**
     * Nom du client.
     *
     * @return string
     */
    public function &getName(): string
    {
        return $this->getString("name");
    }

    /**
     * Type de compte lié au client.
     * Valeur nulle possible, notamment en base de données.
     *
     * @return int
     */
    public function &getRank(): int
    {
        return $this->getInt("rank",
                             0);
    }

    /**
     * {@inheritDoc}
     *
     * @return string
     */
    public function &getZone(): string
    {
        $zone = "SESSION";
        return $zone;
    }

    /**
     * Retourne toutes les information sur le client.
     *
     * @return array
     */
    public function &getData(): array
    {
        return $this->getStorage();
    }

    /**
     * Identifiant du client.
     *
     * @return string
     */
    public function &getIdAsInt(): string
    {
        return $this->getInt("user_id");
    }

    /**
     * Adresse email du client.
     *
     * @return string
     */
    public function &getEmail(): string
    {
        return $this->getString("email");
    }

    /**
     * Détermine si l'utilisateur a des droits.
     *
     * @return bool
     */
    public function hasRank(): bool
    {
        return $this->getRank() > CoreAccessRank::PUBLIC;
    }

    /**
     * Détermine si l'utilisateur est membre.
     *
     * @return bool
     */
    public function hasRegisteredRank(): bool
    {
        return $this->getRank() >= CoreAccessRank::REGISTRED;
    }

    /**
     * Détermine si l'utilisateur est administrateur.
     *
     * @return bool
     */
    public function hasAdminRank(): bool
    {
        return $this->getRank() >= CoreAccessRank::ADMIN;
    }

    /**
     * Détermine si l'utilisateur est administrateur.
     *
     * @return bool
     */
    public function hasAdminWithRightsRank(): bool
    {
        return $this->hasAdminRank() && count($this->getRights()) > 0;
    }

    /**
     * Détermine si l'utilisateur est super administrateur.
     *
     * @return bool
     */
    public function hasSuperAdminRank(): bool
    {
        return $this->hasAdminRank() && CoreAccess::autorize(CoreAccessType::getTypeFromAdmin());
    }

    /**
     * Date d'inscription du client.
     *
     * @return datetime
     */
    public function &getRegistrationDate(): DateTime
    {
        return $this->getDatetime("registration_date");
    }

    /**
     * URL de l'avatar de l'utilisateur.
     * Valeur nulle possible, notamment en base de données.
     *
     * @return string
     */
    public function &getAvatar(): string
    {
        $avatar = $this->getString("avatar");

        if (empty($avatar)) {
            $avatar = "Resources/Avatars/NoPic.png";
        }
        return $avatar;
    }

    /**
     * Site internet du client.
     * Valeur nulle possible, notamment en base de données.
     *
     * @return string
     */
    public function &getWebsite(): string
    {
        return $this->getString("website");
    }

    /**
     * Signature du client.
     * Valeur nulle possible, notamment en base de données.
     *
     * @return string
     */
    public function &getSignature(): string
    {
        return $this->getString("signature");
    }

    /**
     * Template du client.
     * Valeur nulle possible, notamment en base de données.
     *
     * @return string
     */
    public function &getTemplate(): string
    {
        return $this->getString("template");
    }

    /**
     * Affecte le template du client.
     *
     * @param string $template
     * @param bool $force
     */
    public function setTemplate(string $template,
                                bool $force = false)
    {
        if (!$this->hasValue("template") || $force) {
            $this->setDataValue("template",
                                $template);
        }
    }

    /**
     * Langue du client.
     * Valeur nulle possible, notamment en base de données.
     *
     * @return string
     */
    public function &getLangue(): string
    {
        return $this->getString("langue");
    }

    /**
     * Affecte la langue du client.
     *
     * @param string $langue
     * @param bool $force
     */
    public function setLangue(string $langue,
                              bool $force = false)
    {
        if (!$this->hasValue("langue") || $force) {
            $this->setDataValue("langue",
                                $langue);
        }
    }

    /**
     * Retourne la liste des accès que dispose le client.
     *
     * @return CoreAccessType[] array(0 => CoreAccessType)
     */
    public function &getRights(): array
    {
        if (!$this->hasRights()) {
            $this->checkRights();
        }
        return $this->getArray("rights");
    }

    /**
     * Routine de vérification des droits du client.
     */
    private function checkRights()
    {
        $accessTypes = array();

        if ($this->hasRank()) {
            $coreSql = CoreSql::getInstance();

            $coreSql->select(
                    CoreTable::USERS_RIGHTS,
                    array(
                        "zone",
                        "page",
                        "identifiant"),
                    array(
                        "user_id = '" . $this->getId() . "'")
            );

            if ($coreSql->affectedRows() > 0) {
                foreach ($coreSql->fetchArray() as $rights) {
                    $accessTypes[] = CoreAccessType::getTypeFromDatas($rights);
                }
            }
        }

        $this->setDataValue("rights",
                            $accessTypes);
    }

    /**
     * Détermine si les droits du client ont été vérifiés.
     *
     * @return bool
     */
    private function hasRights(): bool
    {
        return $this->hasValue("rights");
    }
}
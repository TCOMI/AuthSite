<?php
use Swagger\Client\Api\UniverseApi;
use Swagger\Client\Api\CharacterApi;
use Swagger\Client\Api\CorporationApi;
use Swagger\Client\Api\AllianceApi;

require_once(realpath(dirname(__FILE__))."/esi/autoload.php");

class EVEHELPERS {

    public static function random_str($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
    {
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $str .= $keyspace[random_int(0, $max)];
        }
        return $str;
    }

    private static function flatten(array $array) {
        $return = array();
        array_walk_recursive($array, function($a) use (&$return) { $return[] = $a; });
        return $return;
    }

    public static function esiIdsToNames($ids) {
        $lookup = array();
        foreach($ids as $key=>$val) {
            $lookup[$val] = true;
        }
        $lookup = array_keys($lookup);
        $esiapi = new ESIAPI();
        $universeapi = new UniverseApi($esiapi);
        try {
            $results = $universeapi->postUniverseNames($lookup, 'tranquility');
        } catch (Exception $e) {
            return null;
        }
        $dict = array();
        foreach($results as $r) {
            $dict[$r->getId()] = $r->getName();
        }
        return $dict;
    }

    public static function getCorpForChar($characterID) {
        $esiapi = new ESIAPI();
        $charapi = new CharacterApi($esiapi);
        try {
            $charinfo = json_decode($charapi->getCharactersCharacterId($characterID, 'tranquility'));
            $corpID = $charinfo->corporation_id;
        } catch (Exception $e) {
            $corpID = null;
        }
        return $corpID;
    }

    public static function getCorpInfo($corpID) {
        $esiapi = new ESIAPI();
        $corpapi = new CorporationApi($esiapi);
        try {
            $corpinfo = json_decode($corpapi->getCorporationsCorporationId($corpID, 'tranquility'), true);
        } catch (Exception $e) {
            $corpinfo = null;
        }
        return $corpinfo;
    }


    public static function getAllyForCorp($corpID) {
        $esiapi = new ESIAPI();
        $corpapi = new CorporationApi($esiapi);
        try {
            $corpinfo = json_decode($corpapi->getCorporationsCorporationId($corpID, 'tranquility'));
            if (isset($corpinfo->alliance_id)) {
                $allyID = $corpinfo->alliance_id;
            } else {
                $allyID = null;
            }
        } catch (Exception $e) {
            $allyID = null;
        }
        return $allyID;
    }

    public static function getAllyInfo($allyID) {
        $esiapi = new ESIAPI();
        $allyapi = new AllianceApi($esiapi);
        try {
            $allyinfo = json_decode($allyapi->getAlliancesAllianceId($allyID, 'tranquility'), true);
        } catch (Exception $e) {
            $allyinfo = null;
        }
        return $allyinfo;
    }

    public static function getAllyHistory($corpid) {
        $esiapi = new ESIAPI();
        $corpapi = new CorporationApi($esiapi);
        $allys = array();
        $lookup = array();
        try {
            $allyHist = ($corpapi->getCorporationsCorporationIdAlliancehistory($corpid, 'tranquility'));
            if (count($allyHist)) {
                foreach($allyHist as $a) {
                    $ally = $a->getAlliance();
                    $temp=array();
                    if ($ally) {
                        $temp['id'] = $ally->getAllianceId();
                        $lookup[$ally->getAllianceId()] = null;
                    }
                    $temp['joined'] = date_format($a->getStartDate(), 'Y-m-d h:i:s');
                    $allys[]=$temp;
                }
            }
            if (count($lookup)) {
                $allyapi = new AllianceApi($esiapi);
                $results = $allyapi->getAlliancesNames(array_keys($lookup), 'tranquility');
                foreach($results as $result) {
                    $lookup[$result->getAllianceId()] = $result->getAllianceName();
                }
                foreach($allys as $i => $ally) {
                    if (isset($ally['id'])) {
                        $allys[$i]['name'] = $lookup[$ally['id']];
                    }
                }
            }
        } catch (Exception $e) {
            $allys = null;
        }
        return $allys;
    }

}
?>

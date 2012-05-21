<?php

require "main-controller.php";

class SitemapController extends MainController
{

    private $_cache = null;

    public function start()
    {
        parent::start();
        $this->_cache = Registry::get("cache");
    }

    public function startAction()
    {
        $this->_view->main(false);
        header("Content-Type: application/xml");

        $this->_pages = array();

        $this->_rubriques = $this->_gabaritManager->getList(ID_VERSION, 0, array(2, 3, 4, 5), TRUE);

        $this->_pages[] = array(
            "path" => '',
            "importance" => "10",
            "lastmod" => ""
        );


        $this->_pages[] = array(
            "path" => 'plan-du-site.html',
            "importance" => "6",
            "lastmod" => ""
        );

        foreach ($this->_rubriques as $ii => $rubrique) {
            $pages = $this->_gabaritManager->getList(ID_VERSION, $rubrique->getMeta('id'), FALSE, 1);
            $rubrique->setChildren($pages);
            foreach ($pages as $page) {
                $this->_pages[] = array(
                    "path" => $rubrique->getMeta('rewriting') . '/' . $page->getMeta('rewriting') . '.html',
                    "importance" => $page->getMeta('importance'),
                    "lastmod" => substr($page->getMeta('date_modif'), 0, 10)
                );
            }
        }

        //Scenario
        $query = "  
                        SELECT DISTINCT `gab_page`.`id`";
        $query .= "  
                        FROM `gab_page`";
        $query .= " 
                        INNER JOIN `main_page_scenario_region` ON  `gab_page`.`id` = `main_page_scenario_region`.`id_gab_page`
                        AND `main_page_scenario_region`.`id_version` = " . ID_VERSION . " AND `main_page_scenario_region`.`suppr` = 0
                        AND `main_page_scenario_region`.`visible` =1";
        $query .= " 
                        INNER JOIN `main_page_scenario_theme` ON  `gab_page`.`id` = `main_page_scenario_theme`.`id_gab_page` 
                        AND `main_page_scenario_theme`.`id_version` = " . ID_VERSION . " AND `main_page_scenario_theme`.`suppr` = 0
                        AND `main_page_scenario_theme`.`visible` =1";

        $query .= " 
                        WHERE `gab_page`.suppr = 0 
                        AND `gab_page`.visible = 1
                        AND `gab_page`.id_version = " . ID_VERSION . "
                    ";

        $scenarios = $this->_db->query($query)->fetchAll();

        $scenariosData = $this->_loadScenarios($scenarios);
        $this->scenarios = $scenariosData["scenarios"];


        if (isset($this->scenarios) && count($this->scenarios) > 0) {
            foreach ($this->scenarios as $scenario) {
                $rewriteParents = $scenario->getParent(1)->getMeta("rewriting") . "/" . $scenario->getParent(0)->getMeta("rewriting") . "/";
                $url = $rewriteParents . $scenario->getMeta('rewriting') . '.html';
                $this->_pages[] = array(
                    "path" => $url,
                    "importance" => $scenario->getMeta('importance'),
                    "lastmod" => $scenario->getMeta('date_modif')
                );
            }
        }
        
        $this->_view->pages = $this->_pages;
    }

    private function _loadScenarios($scenarios)
    {
        $citiesId = array();
        $scenariosLies = array();
        //Scenario joint
        if (count($scenarios) > 0) {
            foreach ($scenarios as $scenario) {
                $scenar = $this->_gabaritManager->getPage(ID_VERSION, $scenario["id"]);
                if (!is_object($scenar))
                    continue;
                $scenariosLies[] = $scenar;
                $villes = array();

                foreach ($scenar->getBlocs("itineraire")->getValues() as $key => $itineraire) {
                    if ($itineraire["villes"] == "")
                        continue;
                    $citiesId = array_merge($citiesId, explode(",", $itineraire["villes"]));
                    $scenar->getBlocs("itineraire")->setValue($key, explode(",", $itineraire["villes"]), "villes");
                    $villes = array_merge($villes, explode(",", $itineraire["villes"]));
                }
                $scenar->setValue("villes", $villes);
            }
        }
        return array(
            "cities" => $citiesId,
            "scenarios" => $scenariosLies,
        );
    }

}

?>
<?php

require_once 'main-controller.php';


class PageController extends MainController
{

    private $_cache = null;


    /**
     * Toujours executé avant l'action.
     *
     * @return void
     */
    public function start()
    {
        parent::start();
        $this->_cache = Registry::get('cache');

    }//end start()


    /**
     * ACOMMENTER.
     *
     * @return void
     */
    public function startAction()
    {

    }//end startAction()


    /**
     * ACOMMENTER.
     *
     * @return void
     */
    public function mypageAction()
    {
        $this->_seo->disableIndex();
        $this->_seo->disableFollow();

    }//end mypageAction()


}//end class

?>
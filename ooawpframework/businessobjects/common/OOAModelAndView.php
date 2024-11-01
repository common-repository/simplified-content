<?php
/**
 * Created by JetBrains PhpStorm.
 * User: nathan
 * Date: 18/09/13
 * Time: 12:01
 * To change this template use File | Settings | File Templates.
 */

include_once OOAWPFRAMEWORK_ROOT . "/exception/OOAViewNotFoundException.php";
include_once OOAWPFRAMEWORK_ROOT . "/businessobjects/util/OTLParser.php";

class OOAModelAndView {

    private $model = array();
    private $viewPath;

    /**
     * Create a model and view
     *
     * @param $viewPath
     * @param array $model
     */
    public function OOAModelAndView($viewPath, $model = array()) {
        $this->viewPath = $viewPath;
        $this->model = $model;
    }

    /**
     * @return mixed
     */
    public function getModel() {
        return $this->model;
    }

    /**
     * @return mixed
     */
    public function getViewPath() {
        return $this->viewPath;
    }


    /**
     * Merge additional model into current model
     *
     * @param $newModel
     */
    public function mergeModel($newModel) {
        if (is_array($newModel))
            $this->model = array_merge($this->model, $newModel);
    }

    /**
     * Evaluate the view using the model.
     *
     */
    public function evaluateView() {

        if (!file_exists($this->viewPath))
            throw new OOAViewNotFoundException($this->viewPath);


        return OTLParser::instance()->parse(file_get_contents($this->viewPath), $this->model);
    }

}
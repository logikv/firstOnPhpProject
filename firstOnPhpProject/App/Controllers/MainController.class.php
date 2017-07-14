<?php
/**
 * Created by PhpStorm.
 * User: bva
 * Date: 11.07.17
 * Time: 16:30
 */

namespace Controllers {

    use Business\User;
    use OnPhp\Controller;
    use OnPhp\Form;
    use OnPhp\HttpRequest;
    use OnPhp\JsonView;
    use OnPhp\Model;
    use OnPhp\ModelAndView;
    use OnPhp\ObjectNotFoundException;
    use OnPhp\Primitive;


    class MainController implements Controller
    {
        /**
         * @return ModelAndView
         **/
        public function handleRequest(HttpRequest $request)
        {
            $model = new Model();
            $model->set('name', 'Vasa');
            $model->set('title', 'Александр Солженицын - Архипелаг ГУЛАГ 01');
            return (new ModelAndView())->setModel($model)->setView('index');
        }


    }
}
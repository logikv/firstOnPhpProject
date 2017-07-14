<?php


namespace Controllers {

    use OnPhp\Controller;
    use OnPhp\HttpRequest;
    use OnPhp\Model;
    use OnPhp\ModelAndView;

    class TempController implements Controller
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
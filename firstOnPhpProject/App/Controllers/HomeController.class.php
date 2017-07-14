<?php
/**
 * Created by PhpStorm.
 * User: bva
 * Date: 11.07.17
 * Time: 14:08
 */

namespace  Controllers {

    use Business\User;
    use OnPhp\Controller;
    use OnPhp\Form;
    use OnPhp\HttpRequest;
    use OnPhp\JsonView;
    use OnPhp\Model;
    use OnPhp\ModelAndView;
    use OnPhp\ObjectNotFoundException;
    use OnPhp\Primitive;

    class HomeController implements Controller
    {

        /**
         * @return ModelAndView
         **/
        public function handleRequest(HttpRequest $request)
        {
            $model = new Model();
            $form = (new Form())
                ->add((new Primitive())->string('name')->setMin(6)->setMax(20)->required())
                ->addMissingLabel('name', 'Обязательно для заполнения')
                ->addWrongLabel('name', 'wrong')
                ->import($request->getGet());

            $form = User::proto()->makeForm();
            echo "<pre>";
            //print_r($form); die;


            if ($form->getErrors())
            {
                return (new ModelAndView())->setView(new JsonView())
                    ->setModel((new Model())->set('success', false)->set('error', $form->getTextualErrorFor('name')));
            }

            $model->set('data', ['name'=> $form->get('name')->getRawValue()]);
            try {
                //Users::dao()->getByName($form->get('name')->getRawValue())
            }catch (ObjectNotFoundException $e)
            {

            }
            $model->merge((new Model())->set('userData', false));

            return (new ModelAndView())->setModel($model)->setView(new JsonView());
        }
    }
}
<?php
/****************************************************************************
 *   Copyright (C) 2007-2008 by Anton E. Lebedevich                         *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/
namespace OnPhp {
    /**
     * Class PrototypedEditor
     * @ingroup Flow
     * @package OnPhp
     */
    abstract class PrototypedEditor extends MethodMappedController
    {
        const COMMAND_SUCCEEDED = 'success';
        const COMMAND_FAILED = 'error';

        protected $subject = null;
        protected $map = null;

        public function __construct(Prototyped $subject)
        {
            $this->subject = $subject;
            $this->map =
                (new MappedForm($this->subject->proto()->makeForm()))
                    ->addSource('id', RequestType::get())
                    ->setDefaultType(RequestType::post());

            $this
                ->setMethodMapping('drop', 'doDrop')
                ->setMethodMapping('take', 'doTake')
                ->setMethodMapping('save', 'doSave')
                ->setMethodMapping('edit', 'doEdit')
                ->setMethodMapping('add', 'doAdd');

            $this->setDefaultAction('edit');
        }

        /**
         * @return ModelAndView
         **/
        public function doDrop(HttpRequest $request)
        {
            $this->map->import($request);
            $form = $this->getForm();

            if ($object = $form->getValue('id')) {
                if ($object instanceof Identifiable) {

                    $this->dropObject($request, $form, $object);

                    return (new ModelAndView())
                        ->setModel(
                            (new Model())
                                ->set('editorResult', self::COMMAND_SUCCEEDED)
                        );

                } else {

                    // already deleted
                    $form->markMissing('id');

                    return (new ModelAndView())
                        ->setModel(
                            (new Model())
                                ->set('editorResult', self::COMMAND_FAILED)
                                ->set('form', $form)
                        );
                }
            } else {
                return (new ModelAndView())
                    ->setModel(
                        (new Model())
                            ->set('editorResult', self::COMMAND_FAILED)
                            ->set('form', $form)
                    );
            }

            Assert::isUnreachable();
        }

        /**
         * @return Form
         **/
        public function getForm()
        {
            return $this->map->getForm();
        }

        protected function dropObject(HttpRequest $request, Form $form, Identifiable $object)
        {
            $object->dao()->drop($object);
        }

        /**
         * @return ModelAndView
         **/
        public function doTake(HttpRequest $request)
        {
            $this->map->import($request);
            $form = $this->getForm();

            if (!$form->getRawValue('id')) {

                $isAdd = true;
                $form->markGood('id');
                $object = clone $this->subject;

            } else {

                $object = $form->getValue('id');
                $isAdd = false;
            }

            if (!$form->getErrors()) {
                $object = $isAdd
                    ? $this->addObject($request, $form, $object)
                    : $this->saveObject($request, $form, $object);

                $editorResult = $form->getErrors()
                    ? self::COMMAND_FAILED
                    : self::COMMAND_SUCCEEDED;

                return
                    (new ModelAndView())
                        ->setModel(
                            (new Model())
                                ->set('id', $object->getId())
                                ->set('subject', $object)
                                ->set('form', $form)
                                ->set('editorResult', $editorResult)
                        );
            } else {
                $model =
                    (new Model())
                        ->set('form', $form)
                        ->set('editorResult', self::COMMAND_FAILED);

                if ($object) {
                    $model->set('subject', $object);
                }

                return (new ModelAndView())->setModel($model);
            }

            Assert::isUnreachable();
        }

        protected function addObject(HttpRequest $request, Form $form, Identifiable $object)
        {
            FormUtils::form2object($form, $object);
            return $object->dao()->add($object);
        }

        protected function saveObject(HttpRequest $request, Form $form, Identifiable $object)
        {
            FormUtils::form2object($form, $object, false);
            return $object->dao()->save($object);
        }

        /**
         * @return ModelAndView
         **/
        public function doSave(HttpRequest $request)
        {
            $this->map->import($request);
            $form = $this->getForm();

            $object = $form->getValue('id');

            if (!$form->getErrors()) {

                $object = $this->saveObject($request, $form, $object);

                $editorResult = $form->getErrors()
                    ? self::COMMAND_FAILED
                    : self::COMMAND_SUCCEEDED;

                return
                    (new ModelAndView())
                        ->setModel(
                            (new Model())
                                ->set('id', $object->getId())
                                ->set('subject', $object)
                                ->set('form', $form)
                                ->set('editorResult', $editorResult)
                        );
            } else {
                $model =
                    (new Model())
                        ->set('form', $form)
                        ->set('editorResult', self::COMMAND_FAILED);

                if ($object) {
                    $model->set('subject', $object);
                }

                return (new ModelAndView())->setModel($model);
            }

            Assert::isUnreachable();
        }

        /**
         * @return ModelAndView
         **/
        public function doEdit(HttpRequest $request)
        {
            $this->map->import($request);
            $form = $this->getForm();

            if ($form->getValue('id')) {
                $object = $form->getValue('id');
            } else {
                $object = clone $this->subject;
            }

            FormUtils::object2form($object, $form);

            $form->dropAllErrors();

            return (new ModelAndView())
                ->setModel(
                    (new Model())
                        ->set('subject', $object)
                        ->set('form', $form)
                );
        }

        /**
         * @return ModelAndView
         **/
        public function doAdd(HttpRequest $request)
        {
            $this->map->import($request);
            $form = $this->getForm();

            $form->markGood('id');
            $object = clone $this->subject;

            if (!$form->getErrors()) {

                $object = $this->addObject($request, $form, $object);

                $editorResult = $form->getErrors()
                    ? self::COMMAND_FAILED
                    : self::COMMAND_SUCCEEDED;

                return
                    (new ModelAndView())
                        ->setModel(
                            (new Model())
                                ->set('id', $object->getId())
                                ->set('subject', $object)
                                ->set('form', $form)
                                ->set('editorResult', $editorResult)
                        );
            } else {
                return
                    (new ModelAndView())
                        ->setModel(
                            (new Model())
                                ->set('form', $form)
                                ->set('subject', $object)
                                ->set('editorResult', self::COMMAND_FAILED)
                        );
            }

            Assert::isUnreachable();
        }
    }
}
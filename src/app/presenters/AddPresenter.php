<?php

/**
 * This file is part of the Weblibre
 *
 * Copyright (c) 2012 Radim Kocman (xkocma03)
 * @author  Radim Kocman 
 */

use Nette\Application\UI;

/**
 * Add new books presenter
 *
 * @author     Radim Kocman
 */
final class AddPresenter extends SignedPresenter
{

  /** @var BrowseCalibre */
  private $calibreModel = NULL;
  
  /**
   * Connect Calibre model
   * @return void
   */
  public function getCalibre() {
    if (!isset($this->calibreModel)) {
      $data = $this->user->getIdentity()->getData();
      $this->calibreModel = new AddCalibre($data['db']);
    }
    
    return $this->calibreModel;
  }
  
  
  
	/**
   * Add data into template
   * @return void
   */
  protected function beforeRender() {
    parent::beforeRender();
    
    // Add navigation
    $this->addNavigation('Add new books', '');
    
    // Set add layout
    $this->setLayout('add');
  }
  
  
  
  /**
   * Add form component factory
   * @return Nette\Application\UI\Form
   */
  protected function createComponentAddForm() {
    $form = new UI\Form;
    $form->setTranslator($this->context->translator);
    
    for($i = 1; $i <= 5; $i++) {
      $form->addGroup();
      $sub = $form->addContainer($i);
      $sub->addUpload('book');
    }
    
    $form->setCurrentGroup(NULL);
    $form->addSubmit('send', 'Add to library');
    
    $form->onValidate[] = callback($this, 'validateAddForm');
    $form->onSuccess[] = callback($this, 'addFormSubmitted');
    return $form;
  }
  
  /**
   * Validate add form
   * @param Nette\Application\UI\Form $form 
   * @return void
   */
  public function validateAddForm($form) {
    $values = $form->getValues();
    
    foreach($values as $key => $value) {
      if ($value['book']->getError() != 4)
        return;
    }
    
    $form->addError('No file uploaded.');
  }
  
  /**
   * Handle submitted add form
   * @param Nette\Application\UI\Form $form 
   * @return void
   */
  public function addFormSubmitted($form) {
    $values = $form->getValues();
    
    if ($this->calibre->addBooks($values)) {
      $msg = $this->context->translator->translate(
        "Books have been successfully added to your library.");
      $this->flashMessage($msg, 'ok');
      $this->redirect('this');
    }
    else {
      $msg = $this->context->translator->translate(
        "Error: Weblibre was unable to add some books into the library!");
      $this->flashMessage($msg, 'error');
    }
  }

}

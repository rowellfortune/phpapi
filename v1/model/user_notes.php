<?php 

class NoteException extends Exception{ };

class UserNontes {
    private $_id;
    private $_note_category;
    private $_note_content;
    private $_completed;

    public function __construct($id, $note_category, $note_content,){
        $this->setID($id);
        $this->setNoteCategory($note_category);
        $this->setNoteContent($note_content);
    }

    public function getID(){ return $this->_id; }

    public function getNoteCategory(){ return $this->_note_category; }

    public function getNoteContent(){ return $this->_note_content; }

    public function setID($id){
        if(($id !== null) && (!is_numeric($id) || $id <= 0 || $id > 9223372936854775807 || $this->_id !== null)){
            throw new NoteException("Note ID error");
        }
        $this->_id = $id;
    }

    public function setNoteCategory($note_category){
        if (strlen($note_category) < 1 || strlen($note_category) > 255) {
            throw new NoteException("Note category error");
        }
        $this->_note_category = $note_category;
    }

    public function setNoteContent($note_content){
        if(($note_content !== null) && (strlen($note_content) > 16777215)) {
            throw new NoteException("Note content error");
        }
        $this->_note_content= $note_content;
    }



    public function returnNoteAsArray(){
        $note = array();
        $note['id'] = $this->getID();
        $note['note_category'] = $this->getNoteCategory();
        $note['note_content'] = $this->getNoteContent();

        return $note;
    }
}
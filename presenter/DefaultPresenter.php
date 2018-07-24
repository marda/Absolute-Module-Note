<?php

namespace Absolute\Module\Note\Presenter;

use Nette\Http\Response;
use Nette\Application\Responses\JsonResponse;

class DefaultPresenter extends NoteBasePresenter {

    /** @var \Absolute\Module\Note\Manager\NoteManager @inject */
    public $noteManager;
    
    /** @var \Absolute\Module\Note\Manager\NoteCRUDManager @inject */
				public $noteCRUDManager;

    /** @var \Absolute\Module\Label\Manager\LabelManager @inject */
    public $labelManager;

    public function startup() {
        parent::startup();
    }

    public function renderDefault($urlId) {
        switch ($this->httpRequest->getMethod()) {
            case 'GET':
                if (isset($urlId))
                    $this->_getRequest($urlId);
                else
                    $this->_getListRequest();
                break;
            case 'POST':
                $this->httpResponse->setCode(Response::S201_CREATED);
                break;
            case 'OPTIONS':
                $this->httpResponse->setCode(Response::S200_OK);
            default:
                break;
        }
        $this->sendResponse(new JsonResponse(
                $this->jsonResponse->toJson(), "application/json;charset=utf-8"
        ));
    }

    public function renderLabel($urlId, $urlId2) {
        switch ($this->httpRequest->getMethod()) {
            case 'GET':
                if (!isset($urlId))
                    $this->httpResponse->setCode(Response::S400_BAD_REQUEST);
                else {
                    if (isset($urlId2)) {
                        $this->_getNoteLabelRequest($urlId, $urlId2);
                    } else {
                        $this->_getNoteLabelListRequest($urlId);
                    }
                }
                break;
            case 'POST':
                $this->_postNoteLabelRequest($urlId, $urlId2);
                break;
            case 'DELETE':
                $this->_deleteNoteLabelRequest($urlId, $urlId2);
            default:
                break;
        }
        $this->sendResponse(new JsonResponse(
                $this->jsonResponse->toJson(), "application/json;charset=utf-8"
        ));
    }

    private function _getRequest($id) {
        $note = $this->noteManager->getById($id);
        if (!$note) {
            $this->httpResponse->setCode(Response::S404_NOT_FOUND);
            return;
        }
        $this->jsonResponse->payload = $note->toJson();
        $this->httpResponse->setCode(Response::S200_OK);
    }

    private function _getListRequest() {
        $notesUser = $this->noteManager->getUserList($this->user->id);
        $notesProjects = $this->noteManager->getUserProjectList($this->user->id);
        $notes = array_unique(array_merge($notesUser, $notesProjects));
        $this->jsonResponse->payload = array_map(function($n) {
            return $n->toJson();
        }, $notes);
        $this->httpResponse->setCode(Response::S200_OK);
    }

    //LABEL
    private function _getNoteLabelListRequest($idNote) {
        $notesList = $this->labelManager->getNoteList($idNote);
        if (!$notesList) {
            $this->httpResponse->setCode(Response::S404_NOT_FOUND);
        } else {
            $this->jsonResponse->payload = $notesList;
            $this->httpResponse->setCode(Response::S200_OK);
        }
    }

    private function _getNoteLabelRequest($noteId, $labelId) {
        $ret = $this->labelManager->getNoteItem($noteId, $labelId);
        if (!$ret) {
            $this->httpResponse->setCode(Response::S404_NOT_FOUND);
        } else {
            $this->jsonResponse->payload = $ret;
            $this->httpResponse->setCode(Response::S200_OK);
        }
    }

    private function _postNoteLabelRequest($urlId, $urlId2) {
        if (!isset($urlId) || !isset($urlId2)) {
            $this->httpResponse->setCode(Response::S400_BAD_REQUEST);
            return;
        }
        $ret = $this->labelManager->labelNoteCreate($urlId, $urlId2);
        if (!$ret) {
            $this->jsonResponse->payload = [];
            $this->httpResponse->setCode(Response::S500_INTERNAL_SERVER_ERROR);
        } else {
            $this->jsonResponse->payload = [];
            $this->httpResponse->setCode(Response::S201_CREATED);
        }
    }

    private function _deleteNoteLabelRequest($urlId, $urlId2) {
        if (!isset($urlId) || !isset($urlId2)) {
            $this->httpResponse->setCode(Response::S400_BAD_REQUEST);
            return;
        }
        $ret = $this->labelManager->labelNoteDelete($urlId, $urlId2);
        if (!$ret) {
            $this->jsonResponse->payload = [];
            $this->httpResponse->setCode(Response::S404_NOT_FOUND);
        } else {
            $this->jsonResponse->payload = [];
            $this->httpResponse->setCode(Response::S200_OK);
        }
    }

}

<?php

namespace Absolute\Module\Note\Presenter;

use Nette\Http\Response;
use Nette\Application\Responses\JsonResponse;

class DefaultPresenter extends NoteBasePresenter
{

    /** @var \Absolute\Module\Note\Manager\NoteManager @inject */
    public $noteManager;

    /** @var \Absolute\Module\Note\Manager\NoteCRUDManager @inject */
    public $noteCRUDManager;

    /** @var \Absolute\Module\Label\Manager\LabelManager @inject */
    public $labelManager;

    public function startup()
    {
        parent::startup();
    }

    public function renderDefault($resourceId)
    {
        switch ($this->httpRequest->getMethod())
        {
            case 'GET':
                if (isset($resourceId))
                    $this->_getRequest($resourceId);
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

    private function _getRequest($id)
    {
        $note = $this->noteManager->getById($id);
        if (!$note)
        {
            $this->httpResponse->setCode(Response::S404_NOT_FOUND);
            return;
        }
        $this->jsonResponse->payload = $note->toJson();
        $this->httpResponse->setCode(Response::S200_OK);
    }

    private function _getListRequest()
    {
        $notesUser = $this->noteManager->getUserList($this->user->id);
        $notesProjects = $this->noteManager->getUserProjectList($this->user->id);
        $notes = array_unique(array_merge($notesUser, $notesProjects));
        $this->jsonResponse->payload = array_map(function($n)
        {
            return $n->toJson();
        }, $notes);
        $this->httpResponse->setCode(Response::S200_OK);
    }

}

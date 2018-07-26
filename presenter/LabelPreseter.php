<?php

namespace Absolute\Module\Note\Presenter;

use Nette\Http\Response;
use Nette\Application\Responses\JsonResponse;

class LabelPresenter extends NoteBasePresenter
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

    public function renderDefault($resourceId, $subResourceId)
    {
        switch ($this->httpRequest->getMethod())
        {
            case 'GET':
                if (!isset($resourceId))
                {
                    $this->httpResponse->setCode(Response::S400_BAD_REQUEST);
                }
                else
                {
                    if (isset($subResourceId))
                    {
                        $this->_getNoteLabelRequest($resourceId, $subResourceId);
                    }
                    else
                    {
                        $this->_getNoteLabelListRequest($resourceId);
                    }
                }
                break;
            case 'POST':
                $this->_postNoteLabelRequest($resourceId, $subResourceId);
                break;
            case 'DELETE':
                $this->_deleteNoteLabelRequest($resourceId, $subResourceId);
            default:
                break;
        }
        $this->sendResponse(new JsonResponse(
                $this->jsonResponse->toJson(), "application/json;charset=utf-8"
        ));
    }

    //LABEL
    private function _getNoteLabelListRequest($idNote)
    {
        $notesList = $this->labelManager->getNoteList($idNote);
        if (!$notesList)
        {
            $this->httpResponse->setCode(Response::S404_NOT_FOUND);
            $this->jsonResponse->payload=$notesList;
        }
        else
        {
            $this->jsonResponse->payload = array_map(function($n)
            {
                return $n->toJson();
            }, $notesList);
            $this->httpResponse->setCode(Response::S200_OK);
        }
    }

    private function _getNoteLabelRequest($noteId, $labelId)
    {
        $ret = $this->labelManager->getNoteItem($noteId, $labelId);
        if (!$ret)
        {
            $this->httpResponse->setCode(Response::S404_NOT_FOUND);
        }
        else
        {
            $this->jsonResponse->payload = $ret->toJson();
            $this->httpResponse->setCode(Response::S200_OK);
        }
    }

    private function _postNoteLabelRequest($urlId, $urlId2)
    {
        $ret = $this->labelManager->labelNoteCreate($urlId, $urlId2);
        if (!$ret)
        {
            $this->jsonResponse->payload = [];
            $this->httpResponse->setCode(Response::S500_INTERNAL_SERVER_ERROR);
        }
        else
        {
            $this->jsonResponse->payload = [];
            $this->httpResponse->setCode(Response::S201_CREATED);
        }
    }

    private function _deleteNoteLabelRequest($urlId, $urlId2)
    {
        $ret = $this->labelManager->labelNoteDelete($urlId, $urlId2);
        if (!$ret)
        {
            $this->jsonResponse->payload = [];
            $this->httpResponse->setCode(Response::S404_NOT_FOUND);
        }
        else
        {
            $this->jsonResponse->payload = [];
            $this->httpResponse->setCode(Response::S200_OK);
        }
    }

}

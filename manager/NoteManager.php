<?php

namespace Absolute\Module\Note\Manager;

use Nette\Database\Context;
use Absolute\Core\Manager\BaseManager;
use Absolute\Module\Note\Entity\Note;
use Absolute\Module\User\Manager\UserManager;
use Absolute\Module\File\Manager\FileManager;

class NoteManager extends BaseManager
{

    private $userManager;
    private $teamManager;
    private $labelManager;
    private $fileManager;

    public function __construct(
    Context $database, UserManager $userManager, FileManager $fileManager /* ,
      \App\Model\TeamManager $teamManager,
      \App\Model\LabelManager $labelManager,
      \App\Model\FileManager $fileManager */
    )
    {
        parent::__construct($database);
        $this->userManager = $userManager;
        $this->fileManager = $fileManager;
        //$this->teamManager = $teamManager;
        //$this->labelManager = $labelManager;
    }

    /* DB TO ENTITY */

    public function _getNote($db)
    {
        if ($db == false)
            return false;

        $object = new Note($db->id, $db->user_id, $db->title, $db->note, $db->color, $db->reminder, $db->archived, $db->created);
        if ($db->ref('file'))
            $object->setImage($this->fileManager->_getFile($db->ref('file')));

        foreach ($db->related('note_user') as $userDb)
        {
            $user = $this->userManager->_getUser($userDb->user);
            if ($user)
                $object->addUser($user);
        }
        foreach ($db->related('note_team') as $teamDb)
        {
            $team = $this->teamManager->_getTeam($teamDb->team);
            if ($team)
                $object->addTeam($team);
        }
        foreach ($db->related('note_category') as $categoryDb)
        {
            $category = $this->teamManager->_getTeam($categoryDb->category);
            if ($category)
                $object->addCategory($category);
        }
        return $object;
    }

    /* INTERNAL/EXTERNAL INTERFACE */

    public function _getById($id)
    {
        $resultDb = $this->database->table('note')->get($id);
        return $this->_getNote($resultDb);
    }

    private function _getList()
    {
        $ret = array();
        $resultDb = $this->database->table('note')->order('created DESC');
        foreach ($resultDb as $db)
        {
            $object = $this->_getNote($db);
            $ret[] = $object;
        }
        return $ret;
    }

    private function _getProjectList($projectId)
    {
        $labels = $this->database->table('project_label')->where('project_id', $projectId)->fetchPairs('label_id', 'label_id');
        $ret = [];
        $resultDb = $this->database->table('note')->where(':project_note.project_id', $projectId)->order('created DESC');
        foreach ($resultDb as $db)
        {
            $object = $this->_getNote($db);
            // Labels
            /*foreach ($db->related('note_label')->where('label_id', $labels) as $labelDb)
            {
                $label = $this->labelManager->_getLabel($labelDb->label);
                if ($label)
                {
                    $object->addLabel($label);
                }
            }*/
            $ret[] = $object;
        }
        return $ret;
    }

    private function _getProjectItem($projectId, $noteId)
    {
        return $this->_getNote($this->database->table('note')->where(':project_note.project_id', $projectId)->where("note_id", $noteId)->fetch());
    }

    public function _noteProjectDelete($projectId, $noteId)
    {
        return $this->database->table('project_note')->where('project_id', $projectId)->where('note_id', $noteId)->delete();
    }

    public function _noteProjectCreate($projectId, $noteId)
    {
        return $this->database->table('project_note')->insert(['project_id' => $projectId, 'note_id' => $noteId]);
    }

    private function _getUserList($userId)
    {
        $labels = $this->database->table('label')->where('user_id', $userId)->fetchPairs('id', 'id');
        $ret = [];
        $resultDb = $this->database->table('note')->where('user_id', $userId)->order('created DESC');
        foreach ($resultDb as $db)
        {
            $object = $this->_getNote($db);
            // Labels
            foreach ($db->related('note_label')->where('label_id', $labels) as $labelDb)
            {
                $label = $this->labelManager->_getLabel($labelDb->label);
                if ($label)
                {
                    $object->addLabel($label);
                }
            }
            $ret[] = $object;
        }
        return $ret;
    }

    private function _getUserProjectList($userId)
    {
        $projects = $this->database->table('project_user')->where('user_id', $userId)->fetchPairs('project_id', 'role');
        $projectLabels = $this->database->table('project_label')->where('project_id', array_keys($projects))->fetchPairs('label_id', 'label_id');
        $userLabels = $this->database->table('label')->where('user_id', $userId)->fetchPairs('id', 'id');
        $labels = array_merge($projectLabels, $userLabels);
        $ret = [];
        $resultDb = $this->database->table('note')->where(':project_note.project_id', array_keys($projects))->order('created DESC');
        foreach ($resultDb as $db)
        {
            $object = $this->_getNote($db);
            $projectDb = $db->related('project_note')->fetch();
            if ($projectDb && array_key_exists($projectDb->project_id, $projects))
            {
                $object->setProjectId($projectDb->project_id);
                $role = $projects[$projectDb->project_id];
                if ($role != "manager" && $role != "owner")
                {
                    $object->setEditable(false);
                }
            }
            else
            {
                $object->setEditable(false);
            }
            // Labels
            foreach ($db->related('note_label')->where('label_id', $labels) as $labelDb)
            {
                $label = $this->labelManager->_getLabel($labelDb->label);
                if ($label)
                {
                    $object->addLabel($label);
                }
            }
            $ret[] = $object;
        }
        return $ret;
    }

    private function _getUserProjectRecentList($userId)
    {
        $projects = $this->database->table('project_user')->where('user_id', $userId)->fetchPairs('project_id', 'project_id');
        $projectLabels = $this->database->table('project_label')->where('project_id', $projects)->fetchPairs('label_id', 'label_id');
        $userLabels = $this->database->table('label')->where('user_id', $userId)->fetchPairs('id', 'id');
        $labels = array_merge($projectLabels, $userLabels);
        $ret = [];
        $resultDb = $this->database->table('note')->where(':project_note.project_id', $projects)->where('UNIX_TIMESTAMP(created) > (UNIX_TIMESTAMP(NOW()) - 24 * 60 * 60)')->order('created DESC');
        foreach ($resultDb as $db)
        {
            $object = $this->_getNote($db);
            // Labels
            foreach ($db->related('note_label')->where('label_id', $labels) as $labelDb)
            {
                $label = $this->labelManager->_getLabel($labelDb->label);
                if ($label)
                {
                    $object->addLabel($label);
                }
            }
            $ret[] = $object;
        }
        return $ret;
    }

    private function _canUserEdit($id, $userId)
    {
        $db = $this->database->table('note')->get($id);
        if (!$db)
        {
            return false;
        }
        if ($db->user_id === $userId)
        {
            return true;
        }
        $projectsInManagement = $this->database->table('project_user')->where('user_id', $userId)->where('role', array('owner', 'manager'))->fetchPairs('project_id', 'project_id');
        $projects = $this->database->table('project_note')->where('note_id', $id)->fetchPairs('project_id', 'project_id');
        return (!empty(array_intersect($projects, $projectsInManagement))) ? true : false;
    }

    private function _getUserCount($userId)
    {
        return $this->database->table('note')->where('note.user_id ? OR :note_user.user_id ?', $userId, $userId)->count("DISTINCT(note.id)");
    }

    private function _getUserPersonalCount($userId)
    {
        return $this->database->table('note')->where('note.user_id', $userId)->count("DISTINCT(note.id)");
    }

    public function getProjectItem($projectId, $noteId)
    {
        return $this->_getProjectItem($projectId, $noteId);
    }

    public function noteProjectDelete($projectId, $noteId)
    {
        return $this->_noteProjectDelete($projectId, $noteId);
    }

    public function noteProjectCreate($projectId, $noteId)
    {
        return $this->_noteProjectCreate($projectId, $noteId);
    }

    /* EXTERNAL METHOD */

    public function getById($id)
    {
        return $this->_getById($id);
    }

    public function getList()
    {
        return $this->_getList();
    }

    public function getProjectList($projectId)
    {
        return $this->_getProjectList($projectId);
    }

    public function getUserList($userId)
    {
        return $this->_getUserList($userId);
    }

    public function getUserProjectList($userId)
    {
        return $this->_getUserProjectList($userId);
    }

    public function getUserCount($userId)
    {
        return $this->_getUserCount($userId);
    }

    public function getUserPersonalCount($userId)
    {
        return $this->_getUserPersonalCount($userId);
    }

    public function getUserProjectRecentList($userId)
    {
        return $this->_getUserProjectRecentList($userId);
    }

    public function canUserEdit($id, $userId)
    {
        return $this->_canUserEdit($id, $userId);
    }

}

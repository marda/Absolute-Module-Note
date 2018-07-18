<?php

namespace Absolute\Module\Note\Manager;

use Absolute\Manager\BaseCRUDManager;

class NoteCRUDManager extends BaseCRUDManager 
{
  public function __construct(\Nette\Database\Context $database) 
  {
    parent::__construct($database);
  }

  // OTHER METHODS

  // CONNECT METHODS

  public function connectUsers($id, $users) 
  {
    $users = array_unique(array_filter($users));
    // DELETE
    $this->database->table('note_user')->where('note_id', $id)->delete();
    // INSERT NEW
    $data = [];
    foreach ($users as $userId) 
    {
      $data[] = array(
        "note_id" => $id,
        "user_id" => $userId,
      );
    }
    
    if (!empty($data))
      $this->database->table('note_user')->insert($data);  

    return true;
  }

  public function connectTeams($id, $teams) 
  {
    $teams = array_unique(array_filter($teams));
    // DELETE
    $this->database->table('note_team')->where('note_id', $id)->delete();
    // INSERT NEW
    $data = [];
    foreach ($teams as $team) 
    {
      $data[] = [
        "team_id" => $team,
        "note_id" => $id,
      ];
    }
    if (!empty($data)) 
      $this->database->table("note_team")->insert($data);  

    return true;
  }

  public function connectCategories($id, $categories) 
  {
    $categories = array_unique(array_filter($categories));
    // DELETE
    $this->database->table('note_category')->where('note_id', $id)->delete();
    // INSERT NEW
    $data = [];
    foreach ($categories as $category) 
    {
      $data[] = [
        "category_id" => $category,
        "note_id" => $id,
      ];
    }
    if (!empty($data)) 
      $this->database->table("note_category")->insert($data);  

    return true;
  }

  public function connectProjectLabels($id, $labels, $projectId) 
  {
    $labels = array_filter($labels);
    $data = [];
    foreach ($labels as $labelId) 
    {
      $data[] = array(
        "note_id" => $id,
        "label_id" => $labelId,
      );
    }
    $projectLabels = $this->database->table('project_label')->where('project_id', $projectId)->fetchPairs('label_id', 'label_id');
    $this->database->table('note_label')->where('note_id', $id)->where('label_id', $projectLabels)->delete();

    if (!empty($data))
      $this->database->table('note_label')->insert($data);          

    return true;
  }

  public function connectUserLabels($id, $labels, $userId) 
  {
    $userLabels = $this->database->table('label')->where('user_id', $userId)->fetchPairs('id', 'id');
    $labels = array_filter($labels);
    $data = [];
    foreach ($labels as $labelId) 
    {
      if (array_key_exists($labelId, $userLabels)) 
      {
        $data[] = array(
          "note_id" => $id,
          "label_id" => $labelId,
        );
      }
    }
    $this->database->table('note_label')->where('note_id', $id)->where('label_id', $userLabels)->delete();
    if (!empty($data))
      $this->database->table('note_label')->insert($data);  

    return true;
  }

  public function connectProject($id, $projectId) 
  {
    $this->database->table('project_note')->where('note_id', $id)->delete();
    return $this->database->table('project_note')->insert(array(
      "note_id" => $id,
      "project_id" => $projectId
    ));  
  }

  public function archive($id) 
  {
    return $this->database->table('note')->where('id', $id)->update(array(
      'archived' => true
    ));       
  }

  public function unarchive($id) 
  {
    return $this->database->table('note')->where('id', $id)->update(array(
      'archived' => false
    ));       
  }

    // CUD METHODS

  public function create($userId, $title, $note, $color, $reminder, $imageId) 
  {
    $result = $this->database->table('note')->insert(array(
      'user_id' => $userId,
      'title' => $title,
      'note' => $note,
      'color' => $color,
      'file_id' => $imageId,
      'reminder' => \App\Classes\DateHelper::validateDate($reminder),
      'created' => new \DateTime(),
    ));
    return $result;
  }

  public function delete($id) 
  {
    $this->database->table("project_note")->where("note_id", $id)->delete();
    $this->database->table("note_label")->where("note_id", $id)->delete();
    $this->database->table('note_category')->where('note_id', $id)->delete();
    $this->database->table('note_team')->where('note_id', $id)->delete();
    $this->database->table('note_user')->where('note_id', $id)->delete();
    return $this->database->table('note')->where('id', $id)->delete();
  }

  public function update($id, $title, $note, $color, $reminder) 
  {
    return $this->database->table('note')->where('id', $id)->update(array(
      'title' => $title,
      'note' => $note,
      'color' => $color,
      'reminder' => \App\Classes\DateHelper::validateDate($reminder),
    ));
  }

  public function updateImage($id, $imageId) 
  {
    return $this->database->table('note')->where('id', $id)->update(array(
      'file_id' => $imageId,
    ));
  }
}


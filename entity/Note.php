<?php

namespace Absolute\Module\Note\Entity;

use Absolute\Core\Entity\BaseEntity;

class Note extends BaseEntity 
{
  private $id;
  private $title;
  private $note;
  private $color;
  private $reminder;
  private $archived;
  private $created;
  private $userId;

  private $users = [];
  private $teams = [];
  private $categories = [];
  private $image = null;
  private $labels = [];

  private $editable = true;
  private $projectId = null;

	public function __construct($id, $userId, $title, $note, $color, $reminder, $archived, $created) 
  {
    $this->id = $id;
    $this->userId = $userId;
		$this->title = $title;
    $this->note = $note;
    $this->color = $color;
    $this->created = $created;
    $this->archived = ($archived) ? true : false;
    $this->reminder = $reminder;
	}

  public function getId() 
  {
    return $this->id;
  }

  public function getUserId()
  {
    return $this->userId;
  }
  
  public function getTitle() 
  {
    return $this->title;
  }

  public function getNote() 
  {
    return $this->note;
  }

  public function getColor() 
  {
    return $this->color;
  }

  public function getReminder() 
  {
    return $this->reminder;
  }

  public function getArchived() 
  {
    return $this->archived;
  }

  public function getCreated() 
  {
    return $this->created;
  }

  public function getImage() 
  {
    return $this->image;
  }

  public function getLabels() 
  {
    return $this->labels;
  }

  public function getProjectId() 
  {
    return $this->projectId;
  }

  public function getUsers() 
  {
    return $this->users;
  }

  public function getTeams()
  {
    return $this->teams;
  }

  public function getCategories()
  {
    return $this->categories;
  }
  
  // IS?

  public function isEditable() 
  {
    return ($this->editable) ? true : false;
  }

  // SETTERS

  public function setEditable($editable) 
  {
    $this->editable = $editable;
  }

  public function setProjectId($projectId) 
  {
    $this->projectId = $projectId;
  }

  public function setImage($image) 
  {
    $this->image = $image;
  }

  // ADDERS

  public function addLabel($label) 
  {
    $this->labels[] = $label;
  }

  public function addUser($user) 
  {
    $this->users[$user->id] = $user;
  }

  public function addTeam($team) 
  {
    $this->teams[$team->id] = $team;
  }

  public function addCategory($category) 
  {
    $this->categories[$category->id] = $category;
  }

  // OTHER METHODS  

  public function toJson() 
  {
    return array(
      "id" => $this->id,
      "title" => $this->title,
      "note" => $this->note,
      "color" => $this->color,
      "reminder" => $this->reminder,
      "archived" => $this->archived,
      "created" => $this->created->format("F j, Y"),
      "users" => array_values(array_map(function($user) { return $user->toJson(); }, $this->users)),
      "teams" => array_values(array_map(function($team) { return $team->toJson(); }, $this->teams)),
      "categories" => array_values(array_map(function($category) { return $category->toJson(); }, $this->categories)), 
    );
  }

  // for array unique
  public function __toString()
  {
    return (string)$this->id;
  }
}


<<?php

class UeduMember_model extends CI_Model{





public function showAll(){



  $query = $this->db->get('uedu_classes');

  if($query->num_rows() > 0){

    return $query->result();
  }

  else{

    return false;
  }
}

public function addUedu_classes($data){



  return $this->db->insert('uedu_classes', $data);
}

public function getUedu_classes($member_id){


$query = $this->db->where('member_id', $member_id);
$query =   $this->db->get('uedu_classes');
  if($query->num_rows() > 0){

    return $query->result();
  }

  else{

    return false;
  }


}

public function updateUedu_classes($member_id, $field){

  $this->db->where('member_id', $member_id);
  $this->db->update('uedu_classes', $field);
  if($this->db->affected_rows() > 0){

    return true;
  }

  else{

    return false;
  }
}

public function update_Uedu_class_by_class_id($class_id, $field){

  $query = $this->db->where('class_id', $class_id);
  $this->db->update('uedu_classes', $field);
  if($this->db->affected_rows() > 0){

    return true;
  }

  else{

    return false;
  }


}

public function get_Uedu_class_by_class_id($class_id){

  $query = $this->db->where('class_id', $class_id);
  $query =   $this->db->get('uedu_classes');
    if($query->num_rows() > 0){

      return $query->result();
    }

    else{

      return false;
    }


}



public function deleteUedu_classes($idx){

  $this->db->where('idx', $idx);
  $this->db->delete('uedu_classes');
  if($this->db->affected_rows() > 0){

    return true;
  }

  else{

    return false;
  }
}


}

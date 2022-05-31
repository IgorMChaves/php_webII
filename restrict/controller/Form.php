<?php
class Form
{
  private $message = "";
  private $error = "";
  public function __construct()
  {
    Transaction::open();
  }
  public function controller()
  {
    $form = new Template("restrict/view/form.html");
    $form->set("id", "");
    $form->set("paciente", "");
    $form->set("idade", "");
    $form->set("diagnostico", "");
    $this->message = $form->saida();
  }
  public function salvar()
  {
    if (isset($_POST['paciente']) && isset($_POST['idade']) && isset($_POST['diagnostico'])) {
      try {
        $conexao = Transaction::get();
        $consultorio = new Crud('consultorio');
        $paciente = $conexao->quote($_POST['paciente']);
        $idade = $conexao->quote($_POST['idade']);
        $diagnostico = $conexao->quote($_POST['diagnostico']);
        if (empty($_POST["id"])) {
          $consultorio->insert("paciente, idade, diagnostico", "$paciente,$idade,$diagnostico");
        } else {
          $id = $conexao->quote($_POST['id']);
          $consultorio->update("paciente=$paciente,idade=$idade,diagnostico=$diagnostico", "id=$id");
        }
        $this->message = $consultorio->getMessage();
        $this->error = $consultorio->getError();
      } catch (Exception $e) {
        $this->message = $e->getMessage();
        $this->error = true;
      }
    }
  }
  public function editar()
  {
    if (isset($_GET['id'])) {
      try {
        $conexao = Transaction::get();
        $id = $conexao->quote($_GET['id']);
        $consultorio = new Crud('consultorio');
        $resultado = $consultorio->select("*", "id=$id");
        if (!$consultorio->getError()) {
          $form = new Template("view/form.html");
          foreach ($resultado[0] as $cod => $valor) {
            $form->set($cod, $valor);
          }
          $this->message = $form->saida();
        } else {
          $this->message = $consultorio->getMessage();
          $this->error = true;
        }
      } catch (Exception $e) {
        $this->message = $e->getMessage();
        $this->error = true;
      }
    }
  }
  public function getMessage()
  {
    if (is_string($this->error)) {
      return $this->message;
    } else {
      $msg = new Template("shared/view/msg.html");
      if ($this->error) {
        $msg->set("cor", "danger");
      } else {
        $msg->set("cor", "success");
      }
      $msg->set("msg", $this->message);
      $msg->set("uri", "?class=Tabela");
      return $msg->saida();
    }
  }
  public function __destruct()
  {
    Transaction::close();
  }
}
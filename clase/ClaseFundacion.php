<?php 
// session_start();
class Fundacion 
{
	public $fundacion;
	private $db;

	function __construct()
	{
		$this->db = Conectar::conexion();
		$this->fundacion = SessionesPet::session_info();
	}

	public function actualizar(){
		$id = $this->fundacion['id'];
		if (isset($_FILES["certificado"])) {
			$imagen=Control::foto($_FILES["certificado"]["tmp_name"]);
			mysqli_query($this->db,"UPDATE fundacion SET certificado='$imagen' WHERE id = '$id'") or die ('errorrrr');
		}
		$insertion = mysqli_query($this->db,"UPDATE fundacion SET nombre='$_POST[nombre]',email='$_POST[correo]',telefono='$_POST[telefono]',direccion= '$_POST[direccion]' WHERE id = '$id'") or die ('errorrrr');
		$_SESSION['user']=$this->fundacion($id);
		header('Location: '.$_SERVER['HTTP_REFERER'] );
	}

	public static function fundaciones(){ //Retorna todas las fundaciones que existen
		$db=Conectar::conexion();
		$resultado = mysqli_query($db,"SElECT * FROM fundacion" ) or die ( "Algo ha ido mal en la consulta a la base de datos");
		return $resultado;
	}

	public static function donacion(){ //Crea  una donacion para una fundacion
			$user = SessionesPet::session_info();
			$fecha = date("Y-m-d H:i:s");
			mysqli_query(Conectar::conexion(),"INSERT INTO donaciones VALUES ('','$_POST[monto]','$fecha','$user[id]','$_POST[id_fundacion]')") or die ('errorrrr');
			header('Location: '.$_SERVER['HTTP_REFERER'] );
	}

	public static function fundacion($id){ //retorna una fundacion
	$resultado = mysqli_query(Conectar::conexion(), "SELECT * FROM fundacion WHERE id='$id' ") or die ( "casi");
		return mysqli_fetch_array($resultado);
	}

	public static function new_fundacion(){
		$insertion = mysqli_query(Conectar::conexion(),"INSERT INTO fundacion values('','$_POST[nombre]','$_POST[email]',MD5('$_POST[password]'),'Inactivo','','$_POST[telefono]','$_POST[direccion]','')") or die ('errorrrr');
		header('Location: '.$_SERVER['HTTP_REFERER'] );
	}

	public function Mis_donaciones(){ //retorna las donaciones que tiene una fundación
		$id = $this->fundacion['id'];
	$resultado = mysqli_query($this->db, "SELECT * FROM donaciones WHERE id_fundacion='$id' ") or die ( "casi");
		 return $resultado;
	}

	public function mis_mascotas(){
		$id_fundacion = $this->fundacion['id'];
		$resultado =  mysqli_query($this->db,"SElECT * FROM mascota WHERE id_fundacion='$id_fundacion'") or die ( "Algo ha ido mal en la consulta a la base de datos");
		return $resultado;
	}

	public function mis_gatos(){
		$id_fundacion = $this->fundacion['id'];
		$resultado =  mysqli_query($this->db,"SElECT * FROM mascota WHERE id_fundacion='$id_fundacion'  && especie='Gato'") or die ( "Algo ha ido mal en la consulta a la base de datos");
		return $resultado;	
	}

	public function mis_perros(){
		$id_fundacion = $this->fundacion['id'];
		$resultado =  mysqli_query($this->db,"SElECT * FROM mascota WHERE id_fundacion='$id_fundacion'  && especie='Perro'") or die ( "Algo ha ido mal en la consulta a la base de datos");
		return $resultado;	
	}
//para verificar niveles de seguridad de las fundaciones.
	public function authorizacion($certificado){
		return $this->fundacion['certificado']===$certificado;
	}

	public function is_fundacion(){
		return isset($this->fundacion['certificado']);
	}

	public function foto(){
		$image = Control::foto($_FILES["imagen"]["tmp_name"]);
		$id = $this->fundacion['id'];
		$insertion = mysqli_query($this->db,"UPDATE fundacion SET foto_fundacion='$image' WHERE id = '$id'") or die ('errorrrr');
		$_SESSION['user']=$this->fundacion($id);
		header('Location: views/perfil/perfilF.php');
	}

	public function Eliminar(){ //eliminar en casacde
		mysqli_query($this->db,"DELETE FROM fundacion WHERE id = '$_POST[id_fundacion]'") or die ('error fundacion');
		mysqli_query($this->db,"DELETE FROM preferencia LEFT JOIN mascota ON 'preferencia'.'id_mascota' = 'mascota'.'id' WHERE id_fundacion = '$_POST[id_fundacion]'") or die ('error en la tabla de preferencia');
		mysqli_query($this->db,"DELETE FROM mascota WHERE id_fundacion = '$_POST[id_fundacion]'") or die ('error eliminar mascotas');
		mysqli_query($this->db,"DELETE FROM donaciones WHERE id_fundacion = '$_POST[id_fundacion]'") or die ('error eliminar donaciones');
		header('Location: '.$_SERVER['HTTP_REFERER'] );
	}

	public function cambiarPass(){
		if(MD5($_POST['password-actual'])!=$this->fundacion['clave'] || $_POST['confirm-password']!=$_POST['password']){
			header('Location: views/perfil/perfilF.php?error-password-actual');
		}else{
			$id = $this->fundacion['id'];
			$insertion = mysqli_query($this->db,"UPDATE fundacion SET clave=MD5('$_POST[password]') WHERE id = '$id'") or die ('errorrrr');
			Control::cerrar_sesion();
		}
	}

	public static function FundacionSlide(){
		$resultado = mysqli_query(Conectar::conexion(), "SELECT * FROM fundacion") or die ( "casi");
		return $resultado;
	}

	public static function FundacionInactiva(){
		$resultado = mysqli_query(Conectar::conexion(), "SELECT * FROM fundacion WHERE estado='Inactivo'") or die ( "casi");
		return $resultado;
	}

	public static function activarFund($id){
		$insertion = mysqli_query(Conectar::conexion(),"UPDATE fundacion SET estado='Activo' WHERE id = '$id'") or die ('errorrrr');
		header('Location: '.$_SERVER['HTTP_REFERER']);
	}

	public static function denegar(){		
		mysqli_query(Conectar::conexion(),"DELETE FROM fundacion WHERE id = '$_POST[id_fundacion]' && estado!='Activo'") or die ('error al denegar');
		header('Location: '.$_SERVER['HTTP_REFERER']);
	}

	public static function recaudo(){
		$insertion = mysqli_fetch_array(mysqli_query(Conectar::conexion(),"SElECT SUM(monto) FROM donaciones "));
		return $insertion;
	}

	public function only_reservada(){ //todas las mascotas reservadas
		$id_fundacion=$this->fundacion['id'];
		$resultado = mysqli_query(Conectar::conexion(), "SELECT * FROM mascota WHERE disponible=0 && solicitud!='Aprobada' && id_fundacion='$id_fundacion' " ) or die ( "casi");
		return $resultado;

	}

	public function nueva_pregunta(){ //Crea  una pregunta frecuente nueva
		$fecha = date("Y-m-d H:i:s");
		$fundacion = $this->fundacion;
		mysqli_query($this->db,"INSERT INTO preguntas VALUES ('','$_POST[pregunta]','$_POST[respuesta]','$fecha','$fundacion[id]')") or die ('errorrrr');
		header('Location: '.$_SERVER['HTTP_REFERER'] );
	}


	public function mis_preguntas(){//retorna todas las preguntas frecuentes que tiene una fundacion.
		$fundacion = $this->fundacion;
		$resultado = mysqli_query($this->db, "SELECT * FROM preguntas WHERE id_fundacion = '$fundacion[id]' " ) or die ( "Algo ha ido mal en la consulta a la base de datos");
		return $resultado;
	}

	public static function DonacionesDe(){
		$obj = array();
		foreach (Fundacion::fundaciones() as $element) {
			$resultado = mysqli_query(Conectar::conexion(), "SELECT * FROM donaciones WHERE id_fundacion='$element[id]'") or die ( "casi");
			$obj[]=mysqli_num_rows($resultado);
		}
		return $obj;
	}
}

 ?>
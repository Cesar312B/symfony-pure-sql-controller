<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;

class LogsController extends AbstractController
{

/**
 * @Route("/", name="app_login")
 */
public function login(Request $request): Response
{
    if ($request->isMethod('POST')) {
        $username = $request->get('_username');
        $password = $request->get('_password');

        // Realizar la verificación de las credenciales utilizando sentencias SQL puras
        $conn = $this->getDoctrine()->getConnection();
        $sql = "SELECT idusuario,user,password FROM usuario WHERE user='$username'";
        $stmt = $conn->executeQuery($sql);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Las credenciales son válidas, iniciar sesión
            // ...

            return $this->redirectToRoute('app_usarios'); // Redirigir a la página principal después del inicio de sesión exitoso
        }

        // Las credenciales son inválidas, mostrar un mensaje de error
        $error = 'Nombre de usuario o contraseña incorrectos';
    }

    return $this->render('security/login.html.twig', [
        'error' => $error ?? null,
        'username' => $username ?? null,
    ]);
}

/**
 * @Route("/nuevo-usuario", name="app_nuevo_usuario")
 */
public function nuevoUsuario(Request $request): Response
{
    if ($request->isMethod('POST')) {
        $username = $request->request->get('_username');
        $password = $request->request->get('_password');

        // Verificar si el usuario ya existe
        $conn = $this->getDoctrine()->getConnection();
        $sql = "SELECT COUNT(*) as count FROM usuario WHERE user = '$username'";
        $stmt = $conn->executeQuery($sql);
        $result = $stmt->fetch();

        if ($result['count'] > 0) {
            // El usuario ya existe, mostrar un mensaje de error
            $error = 'El nombre de usuario ya está en uso.';
        } else {
            // Insertar el nuevo usuario en la base de datos
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuario (user, password) VALUES ('$username', '$hashedPassword')";
            $conn->executeQuery($sql);

            // Redirigir a la página de inicio de sesión o mostrar un mensaje de éxito
            return $this->redirectToRoute('app_login');
        }
    }

    return $this->render('security/registro.html.twig', [
        'error' => $error ?? null,
        'username' => $username ?? null,
    ]);
}

     /**
     * @Route("/usuarios", name="app_usarios")
     */
    public function usarios(): Response
    {
        $conn = $this->getDoctrine()->getConnection();

        // ejecutar la consulta SQL
        $sql = 'select idusuario, nombre, user, password, telefono, correo, Estado FROM m_popular.usuario;';  
        $sql_1='select count(autentificacion.id_autenticacion)as total_logs from autentificacion';
        $sql_2='select count(factura.idFactura)as total_facturas  from factura';
        $sql_3='select count(compra.idcompra) as total_compras  from compra';
        $stmt = $conn->executeQuery($sql);

        // recuperar los resultados
        $resultados = $stmt->fetchAll();


        $stmt1 = $conn->executeQuery($sql_1);

        // recuperar los resultados
        $resultado1 = $stmt1->fetch();

        $stmt2 = $conn->executeQuery($sql_2);

        // recuperar los resultados
        $resultado2 = $stmt2->fetch();

        $stmt3 = $conn->executeQuery($sql_3);

        // recuperar los resultados
        $resultado3 = $stmt3->fetch();

        return $this->render('logs/usarios.html.twig', [
            'resultados' => $resultados,
            'resultado1'=>$resultado1,
            'resultado2'=>$resultado2,
            'resultado3'=>$resultado3
        ]);
    }


/**
 * @Route("/logs/usuario/{idusuario}", name="app_logs")
 */
public function logs($idusuario): Response
{
    $conn = $this->getDoctrine()->getConnection();

    // ejecutar la consulta SQL
      $sql = "SELECT usuario.idusuario, usuario.nombre, autentificacion.fecha 
        FROM usuario INNER JOIN autentificacion 
        ON usuario.idusuario = autentificacion.idusuario 
        WHERE usuario.idusuario = $idusuario";
 
    $stmt=$conn->executeQuery($sql); 

    // recuperar los resultados
    $resultados = $stmt->fetchAll();

    return $this->render('logs/index.html.twig', [
        'resultados' => $resultados
    ]);
}




     /**
     * @Route("/compras/usuario/{idusuario}", name="app_compras")
     */
    public function compras($idusuario): Response
    {
        $conn = $this->getDoctrine()->getConnection();

        // ejecutar la consulta SQL
        $sql = "SELECT compra.idcompra,compra.numero_factura, compra.fecha_emision,proveedores.nombre as provedor, proveedores.telefono,negocio.nombre as negocio FROM compra
        inner join proveedores 
        inner join negocio 
        inner join autentificacion 
        inner join usuario 
        on compra.idProveedores  =proveedores.idProveedores  and compra.id_negocio = negocio.id_negocio and negocio.id_negocio = autentificacion.id_negocio and autentificacion.idusuario=usuario.idusuario where usuario.idusuario=$idusuario";
        $stmt = $conn->executeQuery($sql);

        // recuperar los resultados
        $resultados = $stmt->fetchAll();

        return $this->render('logs/compras.html.twig', [
            'resultados' => $resultados
        ]);
    }

      /**
     * @Route("/facturas/usuario/{idusuario}", name="app_facturas")
     */
    public function facturas($idusuario): Response
    {
        $conn = $this->getDoctrine()->getConnection();

        // ejecutar la consulta SQL
        $sql = "SELECT  factura.idFactura,factura.fecha_emision,cliente.nombre as Clinete,usuario.nombre as Usuario, negocio.nombre, negocio.ruc FROM factura
        inner join cliente
        inner join negocio
        inner join usuario
        on cliente.idCliente = factura.idCliente and negocio.id_negocio= factura.id_negocio and factura.usuario= usuario.idusuario where usuario.idusuario = $idusuario";
        $stmt = $conn->executeQuery($sql);

        // recuperar los resultados
        $resultados = $stmt->fetchAll();

        return $this->render('logs/facturas.html.twig', [
            'resultados' => $resultados
        ]);
    }

    /**
     * @Route("/provedores", name="app_provedores")
     */
    public function provedores(): Response
    {
        $conn = $this->getDoctrine()->getConnection();

        // ejecutar la consulta SQL
        $sql = "SELECT idProveedores, identificacion, nombre, direccion, telefono, email, estado, Cuenta_bancaria FROM proveedores;";
        $stmt = $conn->executeQuery($sql);

        // recuperar los resultados
        $resultados = $stmt->fetchAll();
        return $this->render('logs/provedores.html.twig', [
            'resultados' => $resultados
        ]);
    }


      /**
     * @Route("/productos", name="app_productos")
     */
    public function productos(): Response
    {
        $conn = $this->getDoctrine()->getConnection();

        // ejecutar la consulta SQL
        $sql = "SELECT productos.idProductos, productos.nombre, productos.costo, productos.talla, productos.stock, productos.estado,categoria.Nombre as categoria FROM productos
        inner join categoria 
        on categoria.idCategoria = productos.idCategoria";
        $stmt = $conn->executeQuery($sql);

        // recuperar los resultados
        $resultados = $stmt->fetchAll();
        return $this->render('logs/productos.html.twig', [
            'resultados' => $resultados
        ]);
    }

    /**
     * @Route("/full_compras", name="app_fullcompras")
     */
    public function full_compras(): Response
    {
        $conn = $this->getDoctrine()->getConnection();

        // ejecutar la consulta SQL
        $sql = "select p.idProductos,p.nombre,cd.cantidad, cd.precio,cd.sub_total,c.Nombre as categoria from compra_detalle cd
        inner join productos p 
        inner join categoria c 
        on cd.idProductos = p.idProductos and p.idCategoria = c.idCategoria";
        $stmt = $conn->executeQuery($sql);

        // recuperar los resultados
        $resultados = $stmt->fetchAll();

        return $this->render('logs/full_compras.html.twig', [
            'resultados' => $resultados
        ]);
    }

    /**
     * @Route("/full_facturas", name="app_full_facturas")
     */
    public function full_facturas(): Response
    {
        $conn = $this->getDoctrine()->getConnection();

        // ejecutar la consulta SQL
        $sql = "select p.nombre,cd.cantidad, cd.precio,cd.sub_total,c.Nombre as categoria from factura_detalle cd
        inner join productos p
        inner join categoria c 
        on  cd.idProductos = p.idProductos and c.idCategoria = p.idCategoria;";
        $stmt = $conn->executeQuery($sql);

        // recuperar los resultados
        $resultados = $stmt->fetchAll();

        return $this->render('logs/full_facturas.html.twig', [
            'resultados' => $resultados
        ]);
    }

    /**
     * @Route("/clientes", name="app_clientes")
     */
    public function clientes(): Response
    {
        $conn = $this->getDoctrine()->getConnection();

        // ejecutar la consulta SQL
        $sql = "SELECT idCliente, nombre, identificacion, direccion, telefono, correo, estado, id_negocio FROM m_popular.cliente";
        $stmt = $conn->executeQuery($sql);

        // recuperar los resultados
        $resultados = $stmt->fetchAll();

        return $this->render('logs/clientes.html.twig', [
            'resultados' => $resultados
        ]);
    }


  /**
 * @Route("/detalle_factura/{id}", name="app_detalle_factura")
 */
public function detalle_factura($id): JsonResponse
{
    $conn = $this->getDoctrine()->getConnection();

    // ejecutar la consulta SQL
    $sql = "SELECT factura.idFactura, productos.nombre, productos.costo, productos.talla, categoria.Nombre as categoria, factura_detalle.cantidad,factura_detalle.precio ,factura_detalle.sub_total 
            FROM factura_detalle 
            INNER JOIN factura ON factura.idFactura = factura_detalle.idFactura 
            INNER JOIN productos ON factura_detalle.idProductos = productos.idProductos 
            INNER JOIN categoria ON categoria.idCategoria = productos.idCategoria
            WHERE factura.idFactura = :id";
    
    $stmt = $conn->executeQuery($sql, ['id' => $id]);

    // recuperar los resultados
    $resultados = $stmt->fetchAll();

    return new JsonResponse($resultados);
}

/**
 * @Route("/detalle_compra/{id}", name="app_detalle_compra")
 */
public function detalle_compra($id): JsonResponse
{
    $conn = $this->getDoctrine()->getConnection();

    // ejecutar la consulta SQL
    $sql = "SELECT cv.idcompra, p.nombre, cd.cantidad, cd.precio, cd.sub_total, c.Nombre as categoria 
            FROM compra_detalle cd
            INNER JOIN compra cv ON cv.idcompra = cd.idcompra
            INNER JOIN productos p ON cd.idProductos = p.idProductos
            INNER JOIN categoria c ON p.idCategoria = c.idCategoria
            WHERE cv.idcompra = :id";
    
    $stmt = $conn->executeQuery($sql, ['id' => $id]);

    // recuperar los resultados
    $resultados = $stmt->fetchAll();

    return new JsonResponse($resultados);
}

  
}

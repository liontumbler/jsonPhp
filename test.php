<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>

    <?php
    include('jsonPhp.php');

    $json = new DbJson();

    $arrayTablas = array('usuarios', 'fotos', 'productos');
    //$json->CrearTablas($arrayTablas);


    $camposUsuario = array("nombre" => 'e', "puntos" => '0', "premiosR" => 0, "fotoSubidas" => 0.05);
    //$res = $json->CreateFieldsTable('usuarios', $camposUsuario);

    //$res = $json->UpdateFieldsTableId(1, 'usuarios', $camposUsuario);

    $res = $json->GetTableField('puntos', '2', 'usuarios');//, '>');

    echo 'res--'.$res.'--res';

    print_r($res);
    //var_dump($json->All());
    ?>
</body>
</html>
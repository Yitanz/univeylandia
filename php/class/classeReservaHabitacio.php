<?php
include_once $_SERVER['DOCUMENT_ROOT']."/php/connection.php";

class ReservaHabitacio
{
    //private $idTipusHabitacio;
    private $id_habitacio;
    private $id_usuari;
    private $nom;
    private $cognoms;
    private $doc_iden;
    private $tlf;
    private $num_persones;
    private $dataEntrada;
    private $dataSortida;
    private $id_pensio;
    private $preu_reserva;

    /* CONSTRUCTORS */
    public function __construct()
    {
        $args = func_get_args();
        $num = func_num_args();
        $f='__construct'.$num;
        if (method_exists($this, $f)) {
            call_user_func_array(array($this,$f), $args);
        }
    }

    public function __construct11($id_habitacio, $id_usuari, $nom, $cognoms, $doc_iden, $tlf, $num_persones, $dataEntrada, $dataSortida, $id_pensio, $preu_reserva)
    {
        $this->id_habitacio = $id_habitacio;
        $this->id_usuari = $id_usuari;
        $this->nom = $nom;
        $this->cognoms = $cognoms;
        $this->doc_iden = $doc_iden;
        $this->tlf = $tlf;
        $this->num_persones = $num_persones;
        $this->dataEntrada = $dataEntrada;
        $this->dataSortida = $dataSortida;
        $this->id_pensio = $id_pensio;
        $this->preu_reserva = $preu_reserva;
    }

    /* MÈTODES */
    public function crearReserva()
    {
        try {
            $conn = crearConnexio();

            if ($conn->connect_error) {
                die("Connexió fallida: " . $conn->connect_error);
            }

            $sql = "INSERT INTO RESERVA_HABITACIO (id_habitacio, id_usuari, nom, cognom, document, telefon, num_persones, data_entrada, data_sortida, id_pensio, preu_reserva) VALUES (?,?,?,?,?,?,?,?,?,?,?)";

            $stmt = $conn->prepare($sql);

            if ($stmt==false) {
                //var_dump($stmt);
                die("Secured: Error al introduir el registre.");
                throw new Exception();
            }

            $resultBP = $stmt->bind_param(
            "iissssissid",
            $this->id_habitacio,
            $this->id_usuari,
            $this->nom,
            $this->cognoms,
            $this->doc_iden,
            $this->tlf,
            $this->num_persones,
            $this->dataEntrada,
            $this->dataSortida,
            $this->id_pensio,
            $this->preu_reserva
        );

            if ($resultBP==false) {
                //var_dump($stmt);
                die("Secured2: Error al introduir el registre.");
                throw new Exception();
            }

            $resultEx = $stmt->execute();

            if ($resultEx==false) {
                //var_dump($stmt);
                die("Secured3: Error al introduir el registre.");
                throw new Exception();
            }
            echo '<script>alert("Registre introduit.");</script>';
            $stmt->close();
            $conn->close();
        } catch (Exception $e) {
            echo '<script>alert("Error al introduir el registre.");</script>';
        }
    }


    public static function llistarHabitacionsLliures($data_Entrada, $data_Sortida, $idTipusHabitacio, $id_pensio_seleccionat, $num_persones)
    {
        try {
            $conn = crearConnexio();

            if ($conn->connect_error) {
                die("Connexió fallida: " . $conn->connect_error);
            }

            /* Pas de String a objecte DateTime */
            $DATA_ENTRADA = new DateTime($data_Entrada);
            $DATA_SORTIDA = new DateTime($data_Sortida);

            if ($DATA_SORTIDA<$DATA_ENTRADA || $DATA_SORTIDA==$DATA_ENTRADA) {
                echo '<script>alert("Data de Sortida incorrecta");</script>';
            } else {
                /* Calcul interval de dies que es vol fer la reserva */
                $interval = $DATA_ENTRADA->diff($DATA_SORTIDA);
                $num_dies = $interval->days;
                //echo $num_dies;

                /*$sql = "SELECT HABITACIO.id_habitacio, HABITACIO.num_habitacio, TIPUS_HABITACIO.nom_tipus_habitacio, TIPUS_HABITACIO.preu_tipus_habitacio FROM HABITACIO, TIPUS_HABITACIO WHERE NOT EXISTS
                (SELECT * from RESERVA_HABITACIO WHERE
                (($dataEntrada BETWEEN data_entrada AND date_sub(data_sortida, interval +1 day)) OR
                ($dataSortida BETWEEN date_sub(data_entrada, interval -1 day) AND data_sortida) OR
                (data_entrada <= $dataEntrada AND data_sortida >= $dataSortida) OR (data_entrada >= $dataEntrada AND data_sortida <= $dataSortida)) AND
                HABITACIO.id_habitacio = RESERVA_HABITACIO.id_habitacio) AND (HABITACIO.id_tipus_habitacio = TIPUS_HABITACIO.id_tipus_habitacio AND HABITACIO.id_tipus_habitacio = $idTipusHabitacio)";
                */

                $sql = "SELECT HABITACIO.id_habitacio, HABITACIO.num_habitacio, TIPUS_HABITACIO.nom_tipus_habitacio, TIPUS_HABITACIO.preu_tipus_habitacio
                FROM HABITACIO, TIPUS_HABITACIO
                  WHERE (HABITACIO.id_tipus_habitacio = TIPUS_HABITACIO.id_tipus_habitacio) AND HABITACIO.id_habitacio NOT IN
                    (SELECT id_habitacio FROM RESERVA_HABITACIO WHERE
                    (
                    	('$data_Entrada' BETWEEN data_entrada AND date_sub(data_sortida, INTERVAL +1 day))
                    	OR
                    	('$data_Sortida' BETWEEN date_sub(data_entrada, INTERVAL -1 day) AND data_sortida)
                    	OR
                    	(data_entrada <= '$data_Entrada' AND data_sortida >= '$data_Sortida')
                    	OR
                    	(data_entrada >= '$data_Entrada' AND data_sortida <= '$data_Sortida')
                    ) ORDER BY id_reserva_habitacio
                    )
                    AND ( HABITACIO.id_tipus_habitacio = TIPUS_HABITACIO.id_tipus_habitacio AND HABITACIO.id_tipus_habitacio = '$idTipusHabitacio' )
                    ORDER BY HABITACIO.num_habitacio";
/*
                    $sql = "SELECT HABITACIO.id_habitacio, HABITACIO.num_habitacio, TIPUS_HABITACIO.nom_tipus_habitacio, TIPUS_HABITACIO.preu_tipus_habitacio
                    FROM HABITACIO, TIPUS_HABITACIO
                      WHERE (HABITACIO.id_tipus_habitacio = TIPUS_HABITACIO.id_tipus_habitacio) AND HABITACIO.id_habitacio NOT IN
                        (SELECT id_habitacio FROM RESERVA_HABITACIO WHERE
                        (
                          (? BETWEEN data_entrada AND date_sub(data_sortida, INTERVAL +1 day))
                          OR
                          (? BETWEEN date_sub(data_entrada, INTERVAL -1 day) AND data_sortida)
                          OR
                          (data_entrada <= ? AND data_sortida >= ?)
                          OR
                          (data_entrada >= ? AND data_sortida <= ?)
                        ) ORDER BY id_reserva_habitacio
                        )
                        AND ( HABITACIO.id_tipus_habitacio = TIPUS_HABITACIO.id_tipus_habitacio AND HABITACIO.id_tipus_habitacio = ? )
                        ORDER BY HABITACIO.num_habitacio";
*/
                $result = $conn->query($sql);
                /*$stmt = $conn->prepare($sql);

                if ($stmt==false) {
                    //var_dump($stmt);
                    die("Secured: Error 1");
                    throw new Exception();
                }

                $resultBP = $stmt->bind_param(
                "ssssssi",
                $data_Entrada,
                $data_Sortida,
                $data_Entrada,
                $data_Sortida,
                $data_Entrada,
                $data_Sortida,
                $idTipusHabitacio
                );

                if ($resultBP==false) {
                    //var_dump($stmt);
                    die("Secured2: Error 2");
                    throw new Exception();
                }

                $resultEx = $stmt->execute();
                $resultStmt = $stmt->get_result();

                if ($resultEx==false) {
                    //var_dump($stmt);
                    die("Secured3: 3");
                    throw new Exception();
                }*/

                //if ($resultStmt->num_rows > 0) {
                if ($result->num_rows > 0) {
                    /* Consulta per treure el preu del tipus de pensio seleccionat */
                    $sql2 = "SELECT * FROM PENSIO WHERE id_pensio = $id_pensio_seleccionat";

                    $result2 = $conn->query($sql2);

                    if ($result2->num_rows > 0) {
                        while ($row2 = $result2->fetch_assoc()) {
                            /* Assignacio del preu del tipus de pensio a una variable per poder fer calculs */
                            $preu_pensio_persona = $row2['preu_persona'];
                        }
                    }

                    echo '<span>HABITACIONS LLIURES DEL '.$data_Entrada.' al '.$data_Sortida.'';
                    echo '<div class="table-responsive">';
                    echo '<table class="table table-bordered table-hover table-sm">';
                    echo '<thead class="thead-light">';
                    echo '<tr>';
                    //echo '<th>ID</th>';
                    echo '<th>Número habitació</th>';
                    echo '<th>Tipus habitació</th>';
                    echo '<th>Preu habitació (€)</th>';
                    echo '</tr>';
                    echo '</thead>';

                    //while ($row = $resultStmt->fetch_assoc()) {
                    while ($row = $result->fetch_assoc()) {
                        $id_hab = $row['id_habitacio'];
                        $num_hab = $row['num_habitacio'];
                        //$id_tipus_hab = $row['id_tipus_habitacio'];
                        $tipus_hab = $row['nom_tipus_habitacio'];
                        $preu_habitacio = $row['preu_tipus_habitacio'];
                        /* Calcul del preu TOTAL de la reserva: (preu habitacio + (preu pensio * numero persones)) * numero de dies */
                        $preu_reserva = ($preu_habitacio + ($preu_pensio_persona * $num_persones)) * $num_dies;

                        echo '<tbody>';
                        echo '<tr>';
                        echo '<td style="display:none;">'.$id_hab.'</td>';
                        echo '<td>'.$num_hab.'</td>';
                        echo '<td>'.$tipus_hab.'</td>';
                        echo '<td>'.$preu_habitacio.'</td>';
                        echo '<td><button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalSeleccionar'.$id_hab.'">Seleccionar</button></td>';
                        echo '</tr>';
                        echo '</tbody>';

                        /* MODAL PER SELECCIONAR */
                        echo '<!-- Modal -->';
                        echo '<div class="modal fade" id="modalSeleccionar'.$id_hab.'" tabindex="-1" role="dialog" aria-hidden="true">';
                        echo '  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">';
                        echo '    <div class="modal-content">';
                        echo '      <div class="modal-header">';
                        echo '        <h5 class="modal-title">Seleccionar Habitació</h5>';
                        echo '        <button type="button" class="close" data-dismiss="modal" aria-label="Close">';
                        echo '          <span aria-hidden="true">&times;</span>';
                        echo '        </button>';
                        echo '      </div>';
                        echo '      <div class="modal-body">';
                        echo '        <div class="container">';
                        echo '          <form method="post">';
                        echo '            <div class="form-row">';
                        echo '              <div class="col-md-6 mb-3" style="display: none;">';
                        echo '                <input class="form-control" type="text" value="'.$id_hab.'" name="id_hab_res">';
                        echo '              </div>';
                        echo '              <div class="col-md-6 mb-3">';
                        echo '                <label for="num_habitacio">Número habitació</label>';
                        echo '                <input readonly class="form-control" type="text" value="'.$num_hab.'" name="num_hab_res">';
                        echo '              </div>';
                        echo '              <div class="col-md-6 mb-3">';
                        echo '                <label for="tipus_pensio">Tipus Habitació</label>';
                        echo '                <div class="input-group">';
                        echo '                  <select readonly class="form-control" name="tipus_hab_res" required>';
                        include_once $_SERVER['DOCUMENT_ROOT']."/php/class/classeHabitacio.php";
                        Habitacio::llistarTipusHabitacioModificar($_POST['tipus_hab']);
                        echo '                  </select>';
                        echo '                </div>';
                        echo '              </div>';
                        echo '              <div class="col-md-6 mb-3">';
                        echo '                <label for="data_entrada">Data Entrada</label>';
                        echo '                <input readonly class="form-control" type="date" value="'.$data_Entrada.'" name="data_entrada_res">';
                        echo '              </div>';
                        echo '              <div class="col-md-6 mb-3">';
                        echo '                <label for="data_sortida">Data Sortida</label>';
                        echo '                <input readonly class="form-control" type="date" value="'.$data_Sortida.'" name="data_sortida_res">';
                        echo '              </div>';
                        echo '              <div class="col-md-6 mb-3">';
                        echo '                <label for="tipus_pensio">Tipus Pensió</label>';
                        echo '                <div class="input-group">';
                        echo '                  <select readonly class="form-control" name="tipus_pensio_res" required>';
                        include_once $_SERVER['DOCUMENT_ROOT']."/php/class/classeHabitacio.php";
                        Habitacio::llistarPensioSeleccionat($_POST['tipus_pensio']);
                        echo '                  </select>';
                        echo '                </div>';
                        echo '              </div>';
                        echo '              <div class="col-md-6 mb-3">';
                        echo '                <label for="num_persones">Nº persones</label>';
                        echo '                <input readonly class="form-control" type="text" value="'.$num_persones.'" name="num_persones_res">';
                        echo '              </div>';
                        echo '              <div class="col-md-12 mb-3">';
                        echo '                <label for="preu_reserva">Preu reserva (€)</label>';
                        echo '                <input readonly class="form-control" type="text" value="'.$preu_reserva.'" name="preu_reserva" required>';
                        echo '              </div>';
                        echo '              <div class="col-md-6 mb-3">';
                        echo '                <label for="nom">Nom *</label>';
                        echo '                <input class="form-control" type="text" name="nom_res" required>';
                        echo '              </div>';
                        echo '              <div class="col-md-6 mb-3">';
                        echo '                <label for="cognoms">Cognoms *</label>';
                        echo '                <input class="form-control" type="text" name="cognoms_res" required>';
                        echo '              </div>';
                        echo '              <div class="col-md-6 mb-3">';
                        echo '                <label for="dni">Nº Document Identitat *</label>';
                        echo '                <input class="form-control" type="text" name="dni_res" required>';
                        echo '              </div>';
                        echo '              <div class="col-md-6 mb-3">';
                        echo '                <label for="tlf">Nº Telèfon *</label>';
                        echo '                <input class="form-control" type="tel" name="tlf_res" pattern="[0-9]{9}" required>';
                        echo '                <span class="note">Format: 9 dígits del 0 al 9</span>';
                        echo '              </div>';
                        echo '            </div>';
                        echo '            <input type="submit" class="btn btn-primary" name="reservar" value="Reservar">';
                        echo '            <input type="button" class="btn btn-secondary" data-dismiss="modal" name="cancelar" value="Cancel·lar">';
                        echo '          </form>';
                        echo '        </div>';
                        echo '       </div>';
                        echo '    </div>';
                        echo '  </div>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="alert alert-warning">
                            <strong>Atenció!</strong> No hi ha habitacions lliures.
                          </div>';
                }
                //$stmt->close();

                $conn->close();
            }
        } catch (Exception $e) {
            echo '<script>alert("Error al buscar reserves");</script>';
        }
    }


    public static function llistarReservaHabitacio()
    {
        try {
            $conn = crearConnexio();

            if ($conn->connect_error) {
                die("Connexió fallida: " . $conn->connect_error);
            }

            $sql = "SELECT RESERVA_HABITACIO.id_reserva_habitacio, HABITACIO.id_habitacio, HABITACIO.num_habitacio, HABITACIO.id_tipus_habitacio, TIPUS_HABITACIO.nom_tipus_habitacio, RESERVA_HABITACIO.nom, RESERVA_HABITACIO.cognom, RESERVA_HABITACIO.document, RESERVA_HABITACIO.telefon,
              RESERVA_HABITACIO.num_persones, RESERVA_HABITACIO.data_entrada, RESERVA_HABITACIO.data_sortida, RESERVA_HABITACIO.preu_reserva, RESERVA_HABITACIO.id_pensio, PENSIO.tipus_pensio
              FROM HABITACIO, RESERVA_HABITACIO, PENSIO, TIPUS_HABITACIO WHERE HABITACIO.id_habitacio = RESERVA_HABITACIO.id_habitacio
              AND (RESERVA_HABITACIO.id_pensio = PENSIO.id_pensio AND HABITACIO.id_tipus_habitacio = TIPUS_HABITACIO.id_tipus_habitacio) GROUP BY RESERVA_HABITACIO.data_creacio_registre DESC";

            $result = $conn->query($sql);

            if (!$result) {
                throw new Exception();
            }

            if ($result->num_rows > 0) {
                echo '<div class="table-responsive">';
                echo '<table class="table table-bordered table-hover table-sm">';
                echo '<thead class="thead-light">';
                echo '<tr>';
                //echo '<th>ID</th>';
                echo '<th>Nº hab.</th>';
                echo '<th>Tipus hab.</th>';
                echo '<th>Tipus pensió</th>';
                echo '<th>Nom</th>';
                echo '<th>Cognoms</th>';
                echo '<th>Doc ID</th>';
                echo '<th>Telèfon</th>';
                echo '<th>Nº pers.</th>';
                echo '<th>Entrada</th>';
                echo '<th>Sortida</th>';
                echo '<th>Preu (€)</th>';
                echo '</tr>';
                echo '</thead>';

                while ($row = $result->fetch_assoc()) {
                    $id_reserva_hab = $row['id_reserva_habitacio'];
                    $num_hab = $row['num_habitacio'];
                    $idTipusHabitacio = $row['id_tipus_habitacio'];
                    $tipusHabitacio = $row['nom_tipus_habitacio'];
                    $idPensio = $row['id_pensio'];
                    $tipusPensio = $row['tipus_pensio'];
                    $nomClient = $row['nom'];
                    $cognomsClient = $row['cognom'];
                    $dniClient = $row['document'];
                    $tlfClient = $row['telefon'];
                    $numPersones = $row['num_persones'];
                    $dEntrada = $row['data_entrada'];
                    $dSortida = $row['data_sortida'];
                    $preu = $row['preu_reserva'];

                    echo '<tbody>';
                    echo '<tr>';
                    echo '<td style="display:none;">'.$id_reserva_hab.'</td>';
                    echo '<td>'.$num_hab.'</td>';
                    echo '<td>'.$tipusHabitacio.'</td>';
                    echo '<td>'.$tipusPensio.'</td>';
                    echo '<td>'.$nomClient.'</td>';
                    echo '<td>'.$cognomsClient.'</td>';
                    echo '<td>'.$dniClient.'</td>';
                    echo '<td>'.$tlfClient.'</td>';
                    echo '<td>'.$numPersones.'</td>';
                    echo '<td>'.$dEntrada.'</td>';
                    echo '<td>'.$dSortida.'</td>';
                    echo '<td>'.$preu.'</td>';
                    echo '<td><button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalModificar'.$id_reserva_hab.'">Modificar</button></td>';
                    echo '<td><button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#ModalEliminar'.$id_reserva_hab.'">Eliminar</button></td>';
                    echo '</tr>';
                    echo '</tbody>';

                    /* MODAL PER MODIFICAR
                    echo '<!-- Modal -->';
                    echo '<div class="modal fade" id="modalModificar'.$id_reserva_hab.'" tabindex="-1" role="dialog" aria-hidden="true">';
                    echo '  <div class="modal-dialog modal-dialog-centered modal-md" role="document">';
                    echo '    <div class="modal-content">';
                    echo '      <div class="modal-header">';
                    echo '        <h5 class="modal-title" id="exampleModalLongTitle">Modificar Reserva</h5>';
                    echo '        <button type="button" class="close" data-dismiss="modal" aria-label="Close">';
                    echo '          <span aria-hidden="true">&times;</span>';
                    echo '        </button>';
                    echo '      </div>';
                    echo '      <div class="modal-body">';
                    echo '        <div class="container">';
                    echo '          <form method="post">';
                    echo '            <div class="form-row">';
                    echo '              <div class="col-md-12 mb-3" style="display: none;">';
                    echo '                <input class="form-control" type="text" value="'.$id_reserva_hab.'" name="id_reserva_hab">';
                    echo '              </div>';
                    echo '              <div class="col-md-12 mb-3">';
                    echo '                <label for="num_habitacio">Número habitació</label>';
                    echo '                <input disabled class="form-control" type="text" value="'.$num_hab.'" name="num_hab_mod">';
                    echo '              </div>';
                    echo '            </div>';
                    echo '            <input type="submit" class="btn btn-primary" name="modificar" value="Modificar">';
                    echo '            <input type="button" class="btn btn-secondary" data-dismiss="modal" name="cancelar" value="Cancel·lar">';
                    echo '          </form>';
                    echo '        </div>';
                    echo '       </div>';
                    echo '    </div>';
                    echo '  </div>';
                    echo '</div>';
                    */

                    /* MODAL PER MODIFICAR */
                    echo '<!-- Modal -->';
                    echo '<div class="modal fade" id="modalModificar'.$id_reserva_hab.'" tabindex="-1" role="dialog" aria-hidden="true">';
                    echo '  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">';
                    echo '    <div class="modal-content">';
                    echo '      <div class="modal-header">';
                    echo '        <h5 class="modal-title">Modificar Reserva</h5>';
                    echo '        <button type="button" class="close" data-dismiss="modal" aria-label="Close">';
                    echo '          <span aria-hidden="true">&times;</span>';
                    echo '        </button>';
                    echo '      </div>';
                    echo '      <div class="modal-body">';
                    echo '        <div class="container">';
                    echo '          <form method="post">';
                    echo '            <div class="form-row">';
                    echo '              <div class="col-md-6 mb-3" style="display: none;">';
                    echo '                <input class="form-control" type="text" value="'.$id_reserva_hab.'" name="id_reserva_hab">';
                    echo '              </div>';
                    echo '              <div class="col-md-6 mb-3">';
                    echo '                <label for="num_habitacio">Número habitació</label>';
                    echo '                <input disabled class="form-control" type="text" value="'.$num_hab.'" name="num_hab_res_mod">';
                    echo '              </div>';
                    echo '              <div class="col-md-6 mb-3">';
                    echo '                <label for="tipus_pensio">Tipus Habitació</label>';
                    echo '                <div class="input-group">';
                    echo '                  <select disabled class="form-control" name="tipus_hab_res_mod">';
                    include_once $_SERVER['DOCUMENT_ROOT']."/php/class/classeHabitacio.php";
                    Habitacio::llistarTipusHabitacioModificar($idTipusHabitacio);
                    echo '                  </select>';
                    echo '                </div>';
                    echo '              </div>';
                    echo '              <div class="col-md-6 mb-3">';
                    echo '                <label for="data_entrada">Data Entrada</label>';
                    echo '                <input disabled class="form-control" type="date" value="'.$dEntrada.'" name="data_entrada_mod">';
                    echo '              </div>';
                    echo '              <div class="col-md-6 mb-3">';
                    echo '                <label for="data_sortida">Data Sortida</label>';
                    echo '                <input disabled class="form-control" type="date" value="'.$dSortida.'" name="data_sortida_mod">';
                    echo '              </div>';
                    echo '              <div class="col-md-6 mb-3">';
                    echo '                <label for="tipus_pensio">Tipus Pensió</label>';
                    echo '                <div class="input-group">';
                    echo '                  <select disabled class="form-control" name="tipus_pensio_mod">';
                    include_once $_SERVER['DOCUMENT_ROOT']."/php/class/classeHabitacio.php";
                    Habitacio::llistarPensioSeleccionat($idPensio);
                    echo '                  </select>';
                    echo '                </div>';
                    echo '              </div>';
                    echo '              <div class="col-md-6 mb-3">';
                    echo '                <label for="num_persones">Nº persones</label>';
                    echo '                <input disabled class="form-control" type="text" value="'.$numPersones.'" name="num_persones_mod">';
                    echo '              </div>';
                    echo '              <div class="col-md-12 mb-3">';
                    echo '                <label for="preu_reserva">Preu reserva (€)</label>';
                    echo '                <input disabled class="form-control" type="text" value="'.$preu.'" name="preu_reserva_mod">';
                    echo '              </div>';
                    echo '              <div class="col-md-6 mb-3">';
                    echo '                <label for="nom">Nom *</label>';
                    echo '                <input class="form-control" type="text" value="'.$nomClient.'" name="nom_res_mod" required>';
                    echo '              </div>';
                    echo '              <div class="col-md-6 mb-3">';
                    echo '                <label for="cognoms">Cognoms *</label>';
                    echo '                <input class="form-control" type="text" value="'.$cognomsClient.'" name="cognoms_res_mod" required>';
                    echo '              </div>';
                    echo '              <div class="col-md-6 mb-3">';
                    echo '                <label for="dni">Nº Document Identitat *</label>';
                    echo '                <input class="form-control" type="text" value="'.$dniClient.'" name="dni_res_mod" required>';
                    echo '              </div>';
                    echo '              <div class="col-md-6 mb-3">';
                    echo '                <label for="tlf">Nº Telèfon *</label>';
                    echo '                <input class="form-control" type="tel" value="'.$tlfClient.'" name="tlf_res_mod" pattern="[0-9]{9}" required>';
                    echo '                <span class="note">Format: 9 dígits del 0 al 9</span>';
                    echo '              </div>';
                    echo '            </div>';
                    echo '            <input type="submit" class="btn btn-primary" name="modificar" value="Modificar">';
                    echo '            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>';
                    echo '          </form>';
                    echo '        </div>';
                    echo '       </div>';
                    echo '    </div>';
                    echo '  </div>';
                    echo '</div>';

                    /* MODAL PER ELIMINAR */
                    echo '<!-- Modal -->';
                    echo '<div class="modal fade" id="ModalEliminar'.$id_reserva_hab.'" tabindex="-1" role="dialog" aria-hidden="true">';
                    echo '  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">';
                    echo '    <div class="modal-content">';
                    echo '       <div class="modal-header">';
                    echo '          <h5 class="modal-title">Atenció!</h5>';
                    echo '          <button type="button" class="close" data-dismiss="modal" aria-label="Close">';
                    echo '            <span aria-hidden="true">&times;</span>';
                    echo '          </button>';
                    echo '       </div>';
                    echo '       <div class="modal-body">';
                    echo '          <div class="container">';
                    echo '            <form method="post">';
                    echo '              <div class="form-row">';
                    echo '                <div class="col-md-12 mb-3">';
                    echo '                  <div class="input-group">';
                    echo '                    <input class="form-control" type="text" value="'.$id_reserva_hab.'" name="id_reserva_hab" style="display: none;">';
                    echo '                    <span>Segur que vols eliminar aquesta reserva?</span>';
                    echo '                  </div>';
                    echo '                </div>';
                    echo '              </div>';
                    echo '              <input type="submit" class="btn btn-primary" name="eliminar" value="Eliminar">';
                    echo '              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>';
                    echo '            </form>';
                    echo '          </div>';
                    echo '       </div>';
                    echo '    </div>';
                    echo '  </div>';
                    echo '</div>';
                }
                echo '</table>';
                echo '</div>';
            } else {
              echo '<div class="alert alert-warning">
                      <strong>Atenció!</strong> 0 resultats.
                    </div>';
            }
            $conn->close();
        } catch (Exception $e) {
            echo 'Error al realitzar la consulta.';
        }
    }


    public static function llistarReservaHabitacioBusqueda()
    {
        try {
            $conn = crearConnexio();

            if ($conn->connect_error) {
                die("Connexió fallida: " . $conn->connect_error);
            }

            $filtre = $_POST['busqueda_reserva'];

            $sql = "SELECT RESERVA_HABITACIO.id_reserva_habitacio, HABITACIO.id_habitacio, HABITACIO.num_habitacio, TIPUS_HABITACIO.id_tipus_habitacio, TIPUS_HABITACIO.nom_tipus_habitacio, RESERVA_HABITACIO.nom,
              RESERVA_HABITACIO.cognom, RESERVA_HABITACIO.document, RESERVA_HABITACIO.telefon, RESERVA_HABITACIO.num_persones, RESERVA_HABITACIO.data_entrada, RESERVA_HABITACIO.data_sortida,
              RESERVA_HABITACIO.preu_reserva, RESERVA_HABITACIO.id_pensio, PENSIO.tipus_pensio
              FROM HABITACIO, RESERVA_HABITACIO, PENSIO, TIPUS_HABITACIO WHERE HABITACIO.id_habitacio = RESERVA_HABITACIO.id_habitacio AND
              (RESERVA_HABITACIO.id_pensio = PENSIO.id_pensio AND HABITACIO.id_tipus_habitacio = TIPUS_HABITACIO.id_tipus_habitacio) AND
              (HABITACIO.num_habitacio LIKE '%$filtre%' OR RESERVA_HABITACIO.nom LIKE '%$filtre%' OR RESERVA_HABITACIO.cognom LIKE '%$filtre%' OR
                RESERVA_HABITACIO.document LIKE '%$filtre%' OR RESERVA_HABITACIO.telefon LIKE '%$filtre%') GROUP BY RESERVA_HABITACIO.data_creacio_registre DESC";

            $result = $conn->query($sql);

            if (!$result) {
                throw new Exception();
            }

            if ($result->num_rows > 0) {
                echo '<div class="table-responsive">';
                echo '<table class="table table-bordered table-hover table-sm">';
                echo '<thead class="thead-light">';
                echo '<tr>';
                //echo '<th>ID</th>';
                echo '<th>Nº hab.</th>';
                echo '<th>Tipus hab.</th>';
                echo '<th>Tipus pensió</th>';
                echo '<th>Nom</th>';
                echo '<th>Cognoms</th>';
                echo '<th>Doc ID</th>';
                echo '<th>Telèfon</th>';
                echo '<th>Nº pers.</th>';
                echo '<th>Entrada</th>';
                echo '<th>Sortida</th>';
                echo '<th>Preu (€)</th>';
                echo '</tr>';
                echo '</thead>';

                while ($row = $result->fetch_assoc()) {
                    $id_reserva_hab = $row['id_reserva_habitacio'];
                    $num_hab = $row['num_habitacio'];
                    $idTipusHabitacio = $row['id_tipus_habitacio'];
                    $tipusHabitacio = $row['nom_tipus_habitacio'];
                    $idPensio = $row['id_pensio'];
                    $tipusPensio = $row['tipus_pensio'];
                    $nomClient = $row['nom'];
                    $cognomsClient = $row['cognom'];
                    $dniClient = $row['document'];
                    $tlfClient = $row['telefon'];
                    $numPersones = $row['num_persones'];
                    $dEntrada = $row['data_entrada'];
                    $dSortida = $row['data_sortida'];
                    $preu = $row['preu_reserva'];

                    echo '<tbody>';
                    echo '<tr>';
                    echo '<td style="display:none;">'.$id_reserva_hab.'</td>';
                    echo '<td>'.$num_hab.'</td>';
                    echo '<td>'.$tipusHabitacio.'</td>';
                    echo '<td>'.$tipusPensio.'</td>';
                    echo '<td>'.$nomClient.'</td>';
                    echo '<td>'.$cognomsClient.'</td>';
                    echo '<td>'.$dniClient.'</td>';
                    echo '<td>'.$tlfClient.'</td>';
                    echo '<td>'.$numPersones.'</td>';
                    echo '<td>'.$dEntrada.'</td>';
                    echo '<td>'.$dSortida.'</td>';
                    echo '<td>'.$preu.'</td>';
                    echo '<td><button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalModificar'.$id_reserva_hab.'">Modificar</button></td>';
                    echo '<td><button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#ModalEliminar'.$id_reserva_hab.'">Eliminar</button></td>';
                    echo '</tr>';
                    echo '</tbody>';

                    /* MODAL PER MODIFICAR */
                    echo '<!-- Modal -->';
                    echo '<div class="modal fade" id="modalModificar'.$id_reserva_hab.'" tabindex="-1" role="dialog" aria-hidden="true">';
                    echo '  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">';
                    echo '    <div class="modal-content">';
                    echo '      <div class="modal-header">';
                    echo '        <h5 class="modal-title">Modificar Reserva</h5>';
                    echo '        <button type="button" class="close" data-dismiss="modal" aria-label="Close">';
                    echo '          <span aria-hidden="true">&times;</span>';
                    echo '        </button>';
                    echo '      </div>';
                    echo '      <div class="modal-body">';
                    echo '        <div class="container">';
                    echo '          <form method="post">';
                    echo '            <div class="form-row">';
                    echo '              <div class="col-md-6 mb-3" style="display: none;">';
                    echo '                <input class="form-control" type="text" value="'.$id_reserva_hab.'" name="id_reserva_hab">';
                    echo '              </div>';
                    echo '              <div class="col-md-6 mb-3">';
                    echo '                <label for="num_habitacio">Número habitació</label>';
                    echo '                <input disabled class="form-control" type="text" value="'.$num_hab.'" name="num_hab_res_mod">';
                    echo '              </div>';
                    echo '              <div class="col-md-6 mb-3">';
                    echo '                <label for="tipus_pensio">Tipus Habitació</label>';
                    echo '                <div class="input-group">';
                    echo '                  <select disabled class="form-control" name="tipus_hab_res_mod">';
                    include_once $_SERVER['DOCUMENT_ROOT']."/php/class/classeHabitacio.php";
                    Habitacio::llistarTipusHabitacioModificar($idTipusHabitacio);
                    echo '                  </select>';
                    echo '                </div>';
                    echo '              </div>';
                    echo '              <div class="col-md-6 mb-3">';
                    echo '                <label for="data_entrada">Data Entrada</label>';
                    echo '                <input disabled class="form-control" type="date" value="'.$dEntrada.'" name="data_entrada_mod">';
                    echo '              </div>';
                    echo '              <div class="col-md-6 mb-3">';
                    echo '                <label for="data_sortida">Data Sortida</label>';
                    echo '                <input disabled class="form-control" type="date" value="'.$dSortida.'" name="data_sortida_mod">';
                    echo '              </div>';
                    echo '              <div class="col-md-6 mb-3">';
                    echo '                <label for="tipus_pensio">Tipus Pensió</label>';
                    echo '                <div class="input-group">';
                    echo '                  <select disabled class="form-control" name="tipus_pensio_mod">';
                    include_once $_SERVER['DOCUMENT_ROOT']."/php/class/classeHabitacio.php";
                    Habitacio::llistarPensioSeleccionat($idPensio);
                    echo '                  </select>';
                    echo '                </div>';
                    echo '              </div>';
                    echo '              <div class="col-md-6 mb-3">';
                    echo '                <label for="num_persones">Nº persones</label>';
                    echo '                <input disabled class="form-control" type="text" value="'.$numPersones.'" name="num_persones_mod">';
                    echo '              </div>';
                    echo '              <div class="col-md-12 mb-3">';
                    echo '                <label for="preu_reserva">Preu reserva (€)</label>';
                    echo '                <input disabled class="form-control" type="text" value="'.$preu.'" name="preu_reserva_mod">';
                    echo '              </div>';
                    echo '              <div class="col-md-6 mb-3">';
                    echo '                <label for="nom">Nom *</label>';
                    echo '                <input class="form-control" type="text" value="'.$nomClient.'" name="nom_res_mod" required>';
                    echo '              </div>';
                    echo '              <div class="col-md-6 mb-3">';
                    echo '                <label for="cognoms">Cognoms *</label>';
                    echo '                <input class="form-control" type="text" value="'.$cognomsClient.'" name="cognoms_res_mod" required>';
                    echo '              </div>';
                    echo '              <div class="col-md-6 mb-3">';
                    echo '                <label for="dni">Nº Document Identitat *</label>';
                    echo '                <input class="form-control" type="text" value="'.$dniClient.'" name="dni_res_mod" required>';
                    echo '              </div>';
                    echo '              <div class="col-md-6 mb-3">';
                    echo '                <label for="tlf">Nº Telèfon *</label>';
                    echo '                <input class="form-control" type="tel" value="'.$tlfClient.'" name="tlf_res_mod" pattern="[0-9]{9}" required>';
                    echo '                <span class="note">Format: 9 dígits del 0 al 9</span>';
                    echo '              </div>';
                    echo '            </div>';
                    echo '            <input type="submit" class="btn btn-primary" name="modificar" value="Modificar">';
                    echo '            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>';
                    echo '          </form>';
                    echo '        </div>';
                    echo '       </div>';
                    echo '    </div>';
                    echo '  </div>';
                    echo '</div>';

                    /* MODAL PER ELIMINAR */
                    echo '<!-- Modal -->';
                    echo '<div class="modal fade" id="ModalEliminar'.$id_reserva_hab.'" tabindex="-1" role="dialog" aria-hidden="true">';
                    echo '  <div class="modal-dialog modal-dialog-centered modal-md" role="document">';
                    echo '    <div class="modal-content">';
                    echo '       <div class="modal-header">';
                    echo '          <h5 class="modal-title">Atenció!</h5>';
                    echo '          <button type="button" class="close" data-dismiss="modal" aria-label="Close">';
                    echo '            <span aria-hidden="true">&times;</span>';
                    echo '          </button>';
                    echo '       </div>';
                    echo '       <div class="modal-body">';
                    echo '          <div class="container">';
                    echo '            <form method="post">';
                    echo '              <div class="form-row">';
                    echo '                <div class="col-md-12 mb-3">';
                    echo '                  <div class="input-group">';
                    echo '                    <input class="form-control" type="text" value="'.$id_reserva_hab.'" name="id_reserva_hab" style="display: none;">';
                    echo '                    <span>Segur que vols eliminar aquesta reserva?</span>';
                    echo '                  </div>';
                    echo '                </div>';
                    echo '              </div>';
                    echo '              <input type="submit" class="btn btn-primary" name="eliminar" value="Eliminar">';
                    echo '              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>';
                    echo '            </form>';
                    echo '          </div>';
                    echo '       </div>';
                    echo '    </div>';
                    echo '  </div>';
                    echo '</div>';
                }
                echo '</table>';
                echo '</div>';
            } else {
              echo '<div class="alert alert-warning">
                      <strong>Atenció!</strong> 0 resultats.
                    </div>';
            }
            $conn->close();
        } catch (Exception $e) {
            echo 'Error al realitzar la consulta.';
        }
    }


    public static function modificarReservaHabitacio()
    {
        $conn = crearConnexio();

        if ($conn->connect_error) {
            die("Connexió fallida: " . $conn->connect_error);
        }

        $id_reserva_hab_mod = $_POST['id_reserva_hab'];
        //$num_hab_res_mod = $_POST['num_hab_res_mod'];
        //$tipus_hab_res_mod = $_POST['tipus_hab_res_mod'];
        //$data_entrada_mod = $_POST['data_entrada_mod'];
        //$data_sortida_mod = $_POST['data_sortida_mod'];
        //$tipus_pensio_mod = $_POST['tipus_pensio_mod'];
        //$num_persones_mod = $_POST['num_persones_mod'];
        //$preu_reserva_mod = $_POST['preu_reserva_mod'];
        $nom_res_mod = $_POST['nom_res_mod'];
        $cognoms_res_mod = $_POST['cognoms_res_mod'];
        $dni_res_mod = $_POST['dni_res_mod'];
        $tlf_res_mod = $_POST['tlf_res_mod'];

        //$sql = "UPDATE RESERVA_HABITACIO SET nom=?, cognom=?, document=?, telefon=? WHERE id_reserva_habitacio =?";

        $sql = "UPDATE RESERVA_HABITACIO SET nom='$nom_res_mod', cognom='$cognoms_res_mod', document='$dni_res_mod', telefon='$tlf_res_mod' WHERE id_reserva_habitacio =$id_reserva_hab_mod";

        if ($conn->query($sql)) {
            echo '<script>window.location.href = window.location.href + "?refresh";</script>';
        } else {
            echo '<script>alert("Error!");</script>';
            //echo "Error updating record: " . mysqli_error($conn);
        }
        $conn->close();
/*
        $stmt = $conn->prepare($sql);

        if ($stmt==false) {
            //var_dump($stmt);
            die("Secured: Error al modificar el registre.");
            throw new Exception();
        }

        $resultBP = $stmt->bind_param(
        "ssssi",
        $nom_res_mod,
        $cognoms_res_mod,
        $dni_res_mod,
        $tlf_res_mod,
        $id_reserva_hab_mod
        );

        if ($resultBP==false) {
            //var_dump($stmt);
            die("Secured2: Error al modificar el registre.");
            throw new Exception();
        }

        $resultEx = $stmt->execute();

        if ($resultEx==false) {
            //var_dump($stmt);
            die("Secured3: Error al modificar el registre.");
            throw new Exception();
        }

        if($resultEx==true) {
          echo '<script>window.location.href = window.location.href + "?refresh";</script>';
        }
        else {
            echo '<script>alert("Error!");</script>';
            //echo 'Error';
        }
        $stmt->close();
        $conn->close();
*/
    }

    public static function eliminarReservaHabitacio()
    {
        $conn = crearConnexio();

        if ($conn->connect_error) {
            die("Connexió fallida: " . $conn->connect_error);
        }

        $id_reserva_hab_del = $_POST['id_reserva_hab'];

        $sql = "DELETE FROM RESERVA_HABITACIO WHERE id_reserva_habitacio =$id_reserva_hab_del";

        if ($conn->query($sql)) {
            echo '<script>window.location.href = window.location.href + "?refresh";</script>';
        } else {
            echo '<script>alert("Error!");</script>';
        }
        $conn->close();
    }
}

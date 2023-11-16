<?php
// Se ho l'amount in POST vuol dire che ho richiesto generazione del Pay-By-Link, quindi procedo
if ($_POST["amount"]) {
        
    // Richiesta link PayMail

    $connection = curl_init();

    if ($connection) {

        $requestURL = "https://int-ecommerce.nexi.it/"; // URL
        $requestURI = "ecomm/api/bo/richiestaPayMail"; // URI
        
        // Parametri per calcolo MAC
        $apiKey = "ALIAS_WEB_00073202"; // Alias fornito da Nexi
        $chiaveSegreta = "JW7O76VCXA01QF1GUIUBNNSTI2IZSK2S"; // Chiave segreta fornita da Nexi
        $codiceTransazione = "APIBO_" . date('YmdHis'); // Codice della transazione
        $importo = $_POST["amount"]; // 5000 = 50,00 EURO (indicare la cifra in centesimi)
        $timeout = 4; // Durata in ore del link di pagamento che verrà generato 
        $url = "https://" . filter_input(INPUT_SERVER, 'HTTP_HOST') . "/esito.php"; // URL dove viene rimandato il cliente al termine del pagamento (prefisso necessario http:// oppure https://)
        $urlBack = "https://" . filter_input(INPUT_SERVER, 'HTTP_HOST') . "/back.php"; // URL dove viene rimandato il cliente in caso di annullamento del pagamento (prefisso necessario http:// oppure https://)
        $urlPost = "https://" . filter_input(INPUT_SERVER, 'HTTP_HOST') . "/notifica.php"; // URL verso il quale viene fatta la notifica del pagamento (prefisso necessario http:// oppure https://)
        $timeStamp = (time()) * 1000;

        // Calcolo MAC
        $mac = sha1("apiKey=" . $apiKey . "codiceTransazione=" . $codiceTransazione . "importo=" . $importo . "timeStamp=" . $timeStamp . $chiaveSegreta);

        // Parametri
        $parametri = array(
            // Obbligatori
            'apiKey' => $apiKey,
            'importo' => $importo,
            'codiceTransazione' => $codiceTransazione,
            'timeStamp' => $timeStamp,
            'mac' => $mac
        );

        // Controllo se ho i parametri aggiuntivi opzionali e nel caso li aggiungo
        $parametriAggiuntivi;
        if (!empty($_POST['mail'])) {
            array_push($parametriAggiuntivi, array('mail' => $_POST['mail']));
        }
        if (!empty($_POST['nome'])) {
            array_push($parametriAggiuntivi, array('nome' => $_POST['nome']));
        }
        if (!empty($_POST['cognome'])) {
            array_push($parametriAggiuntivi, array('cognome' => $_POST['cognome']));
        }
        if (!empty($_POST['descrizione'])) {
            array_push($parametriAggiuntivi, array('descrizione' => $_POST['descrizione']));
        }
        if (!empty($parametriAggiuntivi)) {
            array_push($parametri, array('parametriAggiuntivi' => $parametriAggiuntivi))
        }

        curl_setopt_array($connection, array(
            CURLOPT_URL => $requestURL . $requestURI,
            CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => json_encode($parametri),
            CURLOPT_RETURNTRANSFER => 1,
            CURLINFO_HEADER_OUT => true,
            CURLOPT_SSL_VERIFYPEER => 0
        ));

        $json = curl_exec($connection);

        curl_close($connection);

        // Decodifico risposta
        $risposta = json_decode($json, true);

        // Controllo JSON di risposta
        if (json_last_error() === JSON_ERROR_NONE) {

            $MACrisposta = sha1('esito=' . $risposta['esito'] . 'idOperazione=' . $risposta['idOperazione'] . 'timeStamp=' . $risposta['timeStamp'] . $chiaveSegreta);

        } else {
            echo 'Errore nella lettura del JSON di risposta';
        }
    } else {
        echo "Errore curl";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link rel="icon" type="image/x-icon" href="./assets/img/favicon.ico" />

        <title>Dev Shop</title>

        <link rel="stylesheet" href="./assets/css/style.css" />

        <script src="./assets/js/script.js" defer></script>
    </head>
    <body>
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="./assets/img/Nexi_logo.png" alt="NEXI" />
            </div>
            <a class="sidebar-link active" data-target="new-order">New order</a>
            <a class="sidebar-link" data-target="placed-orders">Placed orders</a>
        </div>

        <div class="main">
            <h2 class="header">Dev Shop</h2>

            <!-- NEW ORDER FORM -->
            <div id="new-order" class="page active">
                <h1>New order</h1>

                <form action="index.php" method="post">
                    <div class="order-customer">
                        <div>
                            <h3 class="sub-title">Customer</h3>
                        </div>

                        <div class="field-row">
                            <div class="field-item">
                                <label for="name">Name</label><br />
                                <input type="text" name="nome" id="name" />
                            </div>
                            <div class="field-item">
                                <label for="surname">Surname</label><br />
                                <input type="text" name="cognome" id="surname" />
                            </div>
                            <div class="field-item">
                                <label for="email">Email</label><br />
                                <input type="text" name="mail" id="email" />
                            </div>
                        </div>
                    </div>

                    <div class="order-products">
                        <div>
                            <h3 class="sub-title">Products</h3>
                        </div>

                        <table class="products">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Description</th>
                                    <th>Base price</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <img
                                            src="https://placehold.co/50x50/e53385/white?text=P1"
                                            alt="Product 1"
                                        />
                                    </td>
                                    <td>Product 1</td>
                                    <td>€ 5.50</td>
                                    <td>1</td>
                                    <td>€ 5.50</td>
                                </tr>
                                <tr>
                                    <td>
                                        <img
                                            src="https://placehold.co/50x50/2D32AA/white?text=P2"
                                            alt="Product 2"
                                        />
                                    </td>
                                    <td>Product 2</td>
                                    <td>€ 10.50</td>
                                    <td>1</td>
                                    <td>€ 10.50</td>
                                </tr>
                                <tr>
                                    <td>
                                        <img
                                            src="https://placehold.co/50x50/011638/white?text=P3"
                                            alt="Product 3"
                                        />
                                    </td>
                                    <td>Product 3</td>
                                    <td>€ 15.50</td>
                                    <td>1</td>
                                    <td>€ 15.50</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" style="text-align: right">Total</td>
                                    <td>
                                        <input
                                            type="number"
                                            name="importo"
                                            id="total"
                                            value="31.5"
                                        />
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="field-row">
                        <div class="field-item">
                            <label for="description">Description</label><br />
                            <input
                                type="text"
                                name="descrizione"
                                id="description"
                                placeholder="Order #1"
                            />
                        </div>
                    </div>

                    <input type="submit" class="submit-btn" value="Generate payment link" />
                </form>

                <?php
                // Se ho l'amount in POST vuol dire che ho richiesto generazione del Pay-By-Link quindi ritorno l'esito
                if ($_POST["amount"]) {
                    // Controllo MAC di risposta
                    if ($risposta['mac'] == $MACrisposta) {

                        // Controllo esito
                        if ($risposta['esito'] == 'OK') {
                            echo 'Operazione n. ' . $risposta['idOperazione'] . ' eseguita<br>';
                            echo "Link generato correttamente: " . $risposta['payMailUrl'] . "<br>";
                            echo "<a href ='" . $risposta['payMailUrl'] . "'>VAI AL LINK</a>";
                        } else {
                            echo 'Operazione n. ' . $risposta['idOperazione'] . ' non eseguita. esito ' . $risposta['esito'] . '<br><br>' . json_encode($risposta['errore']);
                        }
                    } else {
                        echo 'Errore nel calcolo del MAC di risposta';
                    }               
                }
                ?>

            </div>
            <!---->

            <!-- PLACED ORDERS LIST-->
            <div id="placed-orders" class="page">
                <h1>Placed orders</h1>

                <div class="order-list">
                    <div class="order">
                        <h3>Order #XX</h3>

                        <div class="order-details">
                            <div>
                                <label> Customer </label>
                                <dl>
                                    <dt>Name</dt>
                                    <dd>...</dd>
                                    <dt>Email</dt>
                                    <dd>...</dd>
                                </dl>
                            </div>

                            <div>
                                <label> Payment Info </label>
                                <dl>
                                    <dt>Card brand</dt>
                                    <dd>...</dd>
                                    <dt>Nationality</dt>
                                    <dd>...</dd>
                                    <dt>Card pan</dt>
                                    <dd>...</dd>
                                    <dt>Expire date</dt>
                                    <dd>...</dd>
                                </dl>
                            </div>

                            <div>
                                <label> Order Info </label>
                                <dl>
                                    <dt>Date</dt>
                                    <dd>...</dd>
                                    <dt>Amount</dt>
                                    <dd>...</dd>
                                    <dt>Transaction code</dt>
                                    <dd>...</dd>
                                    <dt>Result</dt>
                                    <dd>...</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!---->
        </div>

        <footer>
            <div class="footer-content">Nexi DEV Community</div>
        </footer>
    </body>
</html>

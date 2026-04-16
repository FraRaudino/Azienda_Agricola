<?php
include 'config.php';

$messaggio = "";
$clienti  = mysqli_query($conn, "SELECT id_cliente, nome, nickname FROM Clienti ORDER BY nome");
$prodotti = mysqli_query($conn, "SELECT id_prodotto, nome, tipo FROM Prodotti ORDER BY nome");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_cliente  = intval($_POST['id_cliente'] ?? 0);
    $id_prodotto = intval($_POST['id_prodotto'] ?? 0);
    $quantita    = floatval($_POST['quantita'] ?? 0);

    if ($id_cliente <= 0 || $id_prodotto <= 0 || $quantita <= 0) {
        $messaggio = "<div class='message error'>Controlla i dati inseriti.</div>";
    } elseif ($quantita > 99999) {
        $messaggio = "<div class='message error'>Quantità non valida.</div>";
    } else {
        // Verifica che cliente esista
        $stmt_c = mysqli_prepare($conn, "SELECT id_cliente FROM Clienti WHERE id_cliente = ?");
        mysqli_stmt_bind_param($stmt_c, 'i', $id_cliente);
        mysqli_stmt_execute($stmt_c);
        mysqli_stmt_store_result($stmt_c);
        $cliente_ok = mysqli_stmt_num_rows($stmt_c) > 0;
        mysqli_stmt_close($stmt_c);

        if (!$cliente_ok) {
            $messaggio = "<div class='message error'>Cliente non trovato.</div>";
        } else {
            // Leggi tipo prodotto
            $stmt_p = mysqli_prepare($conn, "SELECT tipo FROM Prodotti WHERE id_prodotto = ?");
            mysqli_stmt_bind_param($stmt_p, 'i', $id_prodotto);
            mysqli_stmt_execute($stmt_p);
            $res_p = mysqli_stmt_get_result($stmt_p);
            $riga_prodotto = mysqli_fetch_assoc($res_p);
            mysqli_stmt_close($stmt_p);

            if (!$riga_prodotto) {
                $messaggio = "<div class='message error'>Prodotto non trovato.</div>";
            } else {
                $tipo = $riga_prodotto['tipo'];
                $ok   = true;

                if ($tipo == 'Fresco') {
                    $stmt_f = mysqli_prepare($conn, "SELECT quantita_disponibile FROM Prodotti_Freschi WHERE id_prodotto = ?");
                    mysqli_stmt_bind_param($stmt_f, 'i', $id_prodotto);
                    mysqli_stmt_execute($stmt_f);
                    $res_f = mysqli_stmt_get_result($stmt_f);
                    $riga_fresco = mysqli_fetch_assoc($res_f);
                    mysqli_stmt_close($stmt_f);

                    if ($riga_fresco && $riga_fresco['quantita_disponibile'] !== null) {
                        if ($riga_fresco['quantita_disponibile'] < $quantita) {
                            $messaggio = "<div class='message error'>Quantità fresca insufficiente (disponibile: {$riga_fresco['quantita_disponibile']}).</div>";
                            $ok = false;
                        } else {
                            $upd = mysqli_prepare($conn, "UPDATE Prodotti_Freschi SET quantita_disponibile = quantita_disponibile - ? WHERE id_prodotto = ?");
                            mysqli_stmt_bind_param($upd, 'di', $quantita, $id_prodotto);
                            mysqli_stmt_execute($upd);
                            mysqli_stmt_close($upd);
                        }
                    }
                }

                if ($ok && $tipo == 'Riserva') {
                    $stmt_r = mysqli_prepare($conn, "SELECT peso_totale_disponibile FROM Prodotti_Riserva WHERE id_prodotto = ?");
                    mysqli_stmt_bind_param($stmt_r, 'i', $id_prodotto);
                    mysqli_stmt_execute($stmt_r);
                    $res_r = mysqli_stmt_get_result($stmt_r);
                    $riga_riserva = mysqli_fetch_assoc($res_r);
                    mysqli_stmt_close($stmt_r);

                    if (!$riga_riserva || $riga_riserva['peso_totale_disponibile'] < $quantita) {
                        $disp = $riga_riserva ? $riga_riserva['peso_totale_disponibile'] : 0;
                        $messaggio = "<div class='message error'>Riserva insufficiente (disponibile: {$disp}).</div>";
                        $ok = false;
                    } else {
                        $upd = mysqli_prepare($conn, "UPDATE Prodotti_Riserva SET peso_totale_disponibile = peso_totale_disponibile - ? WHERE id_prodotto = ?");
                        mysqli_stmt_bind_param($upd, 'di', $quantita, $id_prodotto);
                        mysqli_stmt_execute($upd);
                        mysqli_stmt_close($upd);
                    }
                }

                if ($ok && $tipo == 'Confezionato') {
                    $pezzi = intval($quantita);
                    if ($pezzi <= 0) {
                        $messaggio = "<div class='message error'>La quantità deve essere almeno 1 pezzo.</div>";
                        $ok = false;
                    } else {
                        $stmt_conf = mysqli_prepare($conn, "SELECT giacenza_pezzi FROM Prodotti_Confezionati WHERE id_prodotto = ?");
                        mysqli_stmt_bind_param($stmt_conf, 'i', $id_prodotto);
                        mysqli_stmt_execute($stmt_conf);
                        $res_conf = mysqli_stmt_get_result($stmt_conf);
                        $riga_conf = mysqli_fetch_assoc($res_conf);
                        mysqli_stmt_close($stmt_conf);

                        if (!$riga_conf || $riga_conf['giacenza_pezzi'] < $pezzi) {
                            $disp = $riga_conf ? $riga_conf['giacenza_pezzi'] : 0;
                            $messaggio = "<div class='message error'>Prodotto esaurito (disponibile: {$disp} pezzi).</div>";
                            $ok = false;
                        } else {
                            $upd = mysqli_prepare($conn, "UPDATE Prodotti_Confezionati SET giacenza_pezzi = giacenza_pezzi - ? WHERE id_prodotto = ?");
                            mysqli_stmt_bind_param($upd, 'ii', $pezzi, $id_prodotto);
                            mysqli_stmt_execute($upd);
                            mysqli_stmt_close($upd);
                            $quantita = $pezzi;
                        }
                    }
                }

                if ($ok) {
                    $stmt_lp = mysqli_prepare($conn, "SELECT prezzo_unitario FROM Listino_Prezzi WHERE id_prodotto = ? ORDER BY data_inizio_validita DESC LIMIT 1");
                    mysqli_stmt_bind_param($stmt_lp, 'i', $id_prodotto);
                    mysqli_stmt_execute($stmt_lp);
                    $res_lp = mysqli_stmt_get_result($stmt_lp);
                    $riga_prezzo = mysqli_fetch_assoc($res_lp);
                    mysqli_stmt_close($stmt_lp);

                    if (!$riga_prezzo) {
                        $messaggio = "<div class='message error'>Prezzo non definito per questo prodotto.</div>";
                    } else {
                        $prezzo = floatval($riga_prezzo['prezzo_unitario']);
                        $totale = round($prezzo * $quantita, 2);

                        $stmt_v = mysqli_prepare($conn, "INSERT INTO Vendite (id_cliente, data_acquisto, totale_calcolato, totale_pagato) VALUES (?, NOW(), ?, ?)");
                        mysqli_stmt_bind_param($stmt_v, 'idd', $id_cliente, $totale, $totale);

                        if (mysqli_stmt_execute($stmt_v)) {
                            $id_vendita = mysqli_insert_id($conn);
                            $stmt_dv = mysqli_prepare($conn, "INSERT INTO Dettaglio_Vendite (id_vendita, id_prodotto, quantita, prezzo_unitario) VALUES (?, ?, ?, ?)");
                            mysqli_stmt_bind_param($stmt_dv, 'iidd', $id_vendita, $id_prodotto, $quantita, $prezzo);
                            mysqli_stmt_execute($stmt_dv);
                            mysqli_stmt_close($stmt_dv);
                            $messaggio = "<div class='message success'>Vendita completata. Totale: € " . number_format($totale, 2, ',', '.') . "</div>";
                        } else {
                            $messaggio = "<div class='message error'>Errore durante il salvataggio della vendita.</div>";
                        }
                        mysqli_stmt_close($stmt_v);
                    }
                }
            }
        }
    }

    $clienti  = mysqli_query($conn, "SELECT id_cliente, nome, nickname FROM Clienti ORDER BY nome");
    $prodotti = mysqli_query($conn, "SELECT id_prodotto, nome, tipo FROM Prodotti ORDER BY nome");
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registra vendita</title>
    <link rel="stylesheet" href="app.css">
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>Registra vendita</h1>
            <p>Scegli cliente, prodotto e quantità.</p>
        </div>

        <?php echo $messaggio; ?>

        <div class="panel footer-space">
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Cliente</label>
                        <select name="id_cliente" required>
                            <option value="">Seleziona cliente</option>
                            <?php while ($c = mysqli_fetch_assoc($clienti)) { ?>
                                <option value="<?php echo $c['id_cliente']; ?>"><?php echo htmlspecialchars($c['nome']); ?><?php if ($c['nickname'] != '') { echo ' - ' . htmlspecialchars($c['nickname']); } ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Prodotto</label>
                        <select name="id_prodotto" required>
                            <option value="">Seleziona prodotto</option>
                            <?php while ($p = mysqli_fetch_assoc($prodotti)) { ?>
                                <option value="<?php echo $p['id_prodotto']; ?>"><?php echo htmlspecialchars($p['nome']); ?> (<?php echo htmlspecialchars($p['tipo']); ?>)</option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Quantità</label>
                    <input type="number" step="0.01" min="0.01" max="99999" name="quantita" required>
                </div>

                <div class="actions">
                    <button type="submit">Salva vendita</button>
                    <a class="btn btn-light" href="index.php">Torna alla dashboard</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

<?php
http_response_code(404);
include_once '../src/bootstrap.php';

// Inicia a sessão
include APP_ROOT . '/templates/ini_sessao.php';

$titulo = 'Não encontrado';

?>
<?php include APP_ROOT . '/public/includes/header.php'; ?>
<main class="flex-grow-1 bg-body-tertiary">
  <section class="flex-grow-1" id="content">
    <div class="text-center container-fluid pg-n-encontrada">
      <h1>Página não encontrada</h1>
      <p>Tente retornar para a <a href="index">página inicial</a> ou entre em contato através do e-mail
        <a href="mailto:christian.lima@pucrs.br">christian.lima@pucrs.br</a>.
      </p>
    </div>
  </section>
</main>
<?php include APP_ROOT . '/public/includes/footer.php'; ?>
<?php exit ?>
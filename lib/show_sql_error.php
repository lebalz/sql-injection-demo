<?php

function show_sql_error($message)
{
  $message = htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  echo '<div class="alert alert-danger" role="alert" style="color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: .25rem; padding: .75rem 1.25rem; margin: 1rem 0; overflow-wrap: anywhere;">';
  echo '<strong>ERROR:</strong> ' . $message;
  echo '</div>';
}

<div id="popUpDiv">
<b>
<span style="font-size: small;">&nbsp;<a style="color: #FF0000; text-decoration: none;" href="{if $smarty.server.HTTP_HOST == 'www.timetrex.com' OR $smarty.server.HTTP_HOST == 'timetrex.com'}http{if $smarty.server.HTTPS == TRUE}s{/if}://{$config_vars.other.hostname}/interface/{/if}{if isset($config_vars.branding)}flex/{else}BetaTest.php{/if}{if $user_name != '' AND $password != ''}?user_name={$user_name}&password={$password}{/if}">WARNING: This legacy interface will be discontinued by January 31st 2014.<br>Please transition to our new {$APPLICATION_NAME} interface as soon as possible by clicking here!</a>&nbsp;
<br><br><a style="color: #FF0000; text-decoration: none;" href="#" onclick="javascript:document.getElementById('popUpDiv').style.visibility = 'hidden';">[ If you understand, click here to close this message ]</a>
</span>
</b>
</div>

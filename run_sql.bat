@echo off
echo ================================================
echo  MISE EN PLACE DE LA BASE DE DONNEES AGENTEXTRA
echo ================================================
echo.

echo 1. Verification de MySQL...
cd C:\xampp\mysql\bin
if not exist mysql.exe (
    echo ERREUR: MySQL non trouve dans C:\xampp\mysql\bin
    echo Verifiez que XAMPP est correctement installe.
    goto error
)

echo 2. Creation/Mise a jour de la base de donnees...
echo    En cours d'execution, veuillez patienter...
mysql -u root < "C:\xampp\htdocs\agentextra\database_setup.sql"
if %ERRORLEVEL% NEQ 0 (
    echo ERREUR: L'execution du script SQL a echoue.
    goto error
)

echo 3. Verification de la structure...
cd "C:\xampp\htdocs\agentextra"
php check_database.php
if %ERRORLEVEL% NEQ 0 (
    echo AVERTISSEMENT: Des problemes ont ete detectes lors de la verification.
    echo Consultez les messages ci-dessus pour plus de details.
)

echo.
echo ================================================
echo  OPERATION TERMINEE AVEC SUCCES
echo ================================================
echo La base de donnees a ete configuree correctement.
echo Vous pouvez maintenant utiliser l'application.
goto end

:error
echo.
echo ================================================
echo  OPERATION TERMINEE AVEC ERREURS
echo ================================================
echo Consultez les messages d'erreur ci-dessus.
echo Si le probleme persiste, verifiez :
echo  - Que MySQL est en cours d'execution
echo  - Que l'utilisateur root n'a pas de mot de passe
echo  - Que les chemins sont corrects dans ce script

:end
pause 
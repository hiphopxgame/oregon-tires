@echo off
setlocal

set "DIR=C:\SYSTEM MAIN"

echo Registrando componentes en "%DIR%"...
cd /d "%DIR%"

regsvr32  "Seguridad.dll"
regsvr32  "AsistScmII.dll"
regsvr32  "CodeSqlII.dll"
regsvr32  "FormModalII.dll"
regsvr32  "FormPrincII.dll"
regsvr32  "MenPrincII.dll"
regsvr32  "ScmAyudII.dll"
regsvr32  "btListDb.ocx"

echo Listo.
pause

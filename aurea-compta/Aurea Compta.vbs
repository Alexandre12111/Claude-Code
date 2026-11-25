' Lanceur Windows : installe tout au premier lancement, puis ouvre l'application sans fenêtre noire.
Set sh = CreateObject("WScript.Shell")
Set fso = CreateObject("Scripting.FileSystemObject")
dir = fso.GetParentFolderName(WScript.ScriptFullName)
sh.CurrentDirectory = dir
pyw = dir & "\.venv\Scripts\pythonw.exe"

If Not fso.FileExists(pyw) Then
  r = sh.Run("cmd /c py -3 --version", 0, True)
  If r = 0 Then
    py = "py -3"
  Else
    r = sh.Run("cmd /c python --version", 0, True)
    If r <> 0 Then
      MsgBox "Python 3.11 ou plus récent est nécessaire. La page de téléchargement va s'ouvrir : installez Python (cochez « Add Python to PATH »), puis relancez Aurea Compta.", 48, "Aurea Compta"
      sh.Run "https://www.python.org/downloads/windows/"
      WScript.Quit
    End If
    py = "python"
  End If
  MsgBox "Première ouverture : l'installation va durer 1 à 2 minutes. L'application s'ouvrira ensuite toute seule.", 64, "Aurea Compta"
  r = sh.Run("cmd /c " & py & " -m venv .venv && .venv\Scripts\python.exe -m pip install -q -r requirements.txt >> aurea-compta.log 2>&1", 0, True)
  If r <> 0 Or Not fso.FileExists(pyw) Then
    MsgBox "L'installation a échoué. Vérifiez la connexion Internet et relancez. Détail dans aurea-compta.log.", 16, "Aurea Compta"
    WScript.Quit
  End If
End If

sh.Run """" & pyw & """ lancer.py", 0, False

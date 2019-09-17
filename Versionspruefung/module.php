<?
    // Klassendefinition
    class Versionspruefung extends IPSModule {
 
        // Überschreibt die interne IPS_Create($id) Funktion
        public function Create() {
            // Diese Zeile nicht löschen.
            parent::Create();

            $this->RegisterVariableString("AktuelleVersion", $this->Translate("Current Version"));
            $this->RegisterVariableString("VerfuegbareVersion", $this->Translate("Available Version"));

            $this->RegisterPropertyInteger("UpdateIntervall", 12);
            
            $this->RegisterAttributeInteger("LastUpdate", 0);

            $this->RegisterTimer("Update", 0, 'VP_UpdateVersion(' . $this->InstanceID . ');');
 
        }
 
        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() {
            // Diese Zeile nicht löschen
            parent::ApplyChanges();

            $this->SetTimerInterval("Update", $this->ReadPropertyInteger("UpdateIntervall") * 60 * 60 * 1000);

            $this->UpdateVersion();
        }

        public function GetConfigurationForm() {
            $jsonForm = json_decode(file_get_contents(__DIR__ . "/form.json"), true);

            $jsonForm["actions"][0]["caption"] = sprintf($this->Translate("Last Update: %s"), date("d.m.Y - H:i:s", $this->ReadAttributeInteger("LastUpdate")));

            return json_encode($jsonForm);
        }
 
        /**
        * Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
        * Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wiefolgt zur Verfügung gestellt:
        *
        * VP_UpdateVersion($id);
        *
        */
        public function UpdateVersion() {

            $this->SetValue("AktuelleVersion", IPS_GetKernelVersion());

            $rawData = file_get_contents('https://apt.symcon.de/dists/stable/win/binary-i386/Packages');
            $xml = simplexml_load_string($rawData);
            $version = $xml->channel->item->enclosure->attributes('sparkle', true)->shortVersionString;
            $this->SetValue("VerfuegbareVersion", strval($version));

            $updateTime = time();
            $this->WriteAttributeInteger("LastUpdate", $updateTime);
            $this->UpdateFormField("UpdateLabel", "caption", "Last Update: " . date("d.m.Y - H:i:s", $updateTime));
        }
    }
?>
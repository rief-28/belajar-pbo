<?php
class Hewan {
    public function suara(): string {
        return "Hewan mengeluarkan suara";
    }
}

class Kucing extends Hewan {
    public function suara(): string {
        return "Meong!";
    }
}

class Anjing extends Hewan {
    public function suara(): string {
        return "Guk guk!";
    }
}

$hewan = new Hewan();
$kucing = new Kucing();
$anjing = new Anjing();

echo $hewan->suara() . PHP_EOL;
echo $kucing->suara() . PHP_EOL;
echo $anjing->suara() . PHP_EOL;

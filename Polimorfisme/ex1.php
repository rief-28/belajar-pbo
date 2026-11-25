<?php
class Hewan {
    public function suara(): string { return "Suara hewan"; }
}
class Kucing extends Hewan {
    public function suara(): string { return "Meong!"; }
}
class Anjing extends Hewan {
    public function suara(): string { return "Guk guk!"; }
}
class Ayam extends Hewan {
    public function suara(): string { return "Kukuruyuk!"; }
}

function bunyikan(Hewan $objek) {
    echo $objek->suara() . "<br>";
}

$daftarHewan = [new Kucing(), new Anjing(), new Ayam()];
foreach ($daftarHewan as $hewan) {
    bunyikan($hewan);
}

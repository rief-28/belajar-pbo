class Hewan {
    public void suara() { System.out.println("Suara hewan"); }
}
class Kucing extends Hewan {
    @Override
    public void suara() { System.out.println("Meong!"); }
}
class Anjing extends Hewan {
    @Override
    public void suara() { System.out.println("Guk guk!"); }
}
class Ayam extends Hewan {
    @Override
    public void suara() { System.out.println("Kukuruyuk!"); }
}
public class Main {
    static void bunyikan(Hewan objek) { objek.suara(); }
    public static void main(String[] args) {
        Hewan[] daftarHewan = {
            new Kucing(), new Anjing(), new Ayam()
        };
        for (Hewan hewan : daftarHewan) {
            bunyikan(hewan);
        }
    }
}


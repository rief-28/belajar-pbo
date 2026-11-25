#include <iostream>
using namespace std;

class Hewan {
public:
    virtual void suara() { cout << "Suara hewan" << endl; }
    virtual ~Hewan() = default;
};
class Kucing : public Hewan {
public:
    void suara() override { cout << "Meong!" << endl; }
};
class Anjing : public Hewan {
public:
    void suara() override { cout << "Guk guk!" << endl; }
};
class Ayam : public Hewan {
public:
    void suara() override { cout << "Kukuruyuk!" << endl; }
};
void bunyikan(Hewan &objek) { objek.suara(); }
int main() {
    Kucing kucing; Anjing anjing; Ayam ayam;
    bunyikan(kucing); bunyikan(anjing); bunyikan(ayam);
    return 0;
}

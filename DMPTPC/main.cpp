#include "dmptpc.h"
#include <QApplication>

int main(int argc, char *argv[])
{
    QApplication a(argc, argv);
    DMPTPC w;
    w.show();
    return a.exec();
}

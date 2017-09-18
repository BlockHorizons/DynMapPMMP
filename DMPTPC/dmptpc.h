#ifndef DMPTPC_H
#define DMPTPC_H

#include <QMainWindow>
#include <QFileDialog>
#include <QFile>

namespace Ui {
class DMPTPC;
}

class DMPTPC : public QMainWindow
{
    Q_OBJECT

public:
    explicit DMPTPC(QWidget *parent = 0);
    ~DMPTPC();

private slots:
    void on_open_tp_button_clicked();
    void on_close_clicked();
    void console(const QString &log);
    bool registerBlockFileName(const std::string &key, const std::string &val);
    void registerAllBlockFiles();
    void startConversionProcess(const std::string &zipPath);
    QString getTextFromFile(const QString &file);

private:
    Ui::DMPTPC *ui;
};

#endif // DMPTPC_H

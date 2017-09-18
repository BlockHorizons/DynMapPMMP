#include "dmptpc.h"
#include "ui_dmptpc.h"
#include <vector>
#include <QDebug>
#include <algorithm>

using namespace std;

std::map<std::string, std::string> blockFileNameList;
std::vector<std::string> logs(10);

DMPTPC::DMPTPC(QWidget *parent) :
    QMainWindow(parent),
    ui(new Ui::DMPTPC)
{
    setWindowFlags(Qt::Widget | Qt::FramelessWindowHint);
    setParent(0);
    setAttribute(Qt::WA_NoSystemBackground, true);
    setAttribute(Qt::WA_TranslucentBackground, true);
    setAttribute(Qt::WA_PaintOnScreen);
    ui->setupUi(this);
    this->statusBar()->setSizeGripEnabled(false);
    this->setStyleSheet("background: /*rgba(127,255,212,1)*/ rgba(66, 66, 244, 1);");
    ui->title->setStyleSheet("");
    ui->open_tp_button->setStyleSheet("QPushButton{ background-color: rgba(0, 119, 255, 1); color: white; border: none; font: 8pt \"Verdana\"; }");
    ui->consoleText->setStyleSheet("background-color: rgba(33, 33, 33, 1); color:#ffe500; font: 15px \"Verdana\"; padding: 5px;");
    ui->progressBar->setStyleSheet("QProgressBar {background-color: #FFF; color: #333; border-radius: 5px; font: 8pt \"Verdana\"; }"
                                   "QProgressBar::chunk { border-radius: 5px; background-color: rgba(127,255,212, 0.8); }");
    ui->close->setStyleSheet("background-color: rgba(0, 119, 255, 1); color: white; border: none;");
    ui->consoleText->setAlignment(Qt::AlignLeft | Qt::AlignBottom);
    ui->consoleText->setText("Thanks for using DynMapPMMP <br/> By Sandertv, xBeastMode and HimbeersaftLP");
    ui->tabs->setTabText(0, "About");
    ui->tabs->setTabText(1, "Contact");
    ui->tabs->setTabText(2, "Terms Of Service");
    ui->tab->setStyleSheet("background-color: rgba(0, 119, 255, 1); color: #FFF; font: 8pt \"Verdana\";");
    ui->tab_2->setStyleSheet("background-color: rgba(0, 119, 255, 1); color: #FFF; font: 8pt \"Verdana\";");
    ui->tab_3->setStyleSheet("background-color: rgba(0, 119, 255, 1); color: #FFF; font: 8pt \"Verdana\";");
    ui->tabs->setStyleSheet("background-color: rgba(0, 119, 255, 1); color: #000; border: 1px solid #FFF; font: 8pt \"Verdana\";");
    ui->tab1Text->setAlignment(Qt::AlignLeft | Qt::AlignTop);
    ui->tab2Text->setAlignment(Qt::AlignLeft | Qt::AlignTop);
    ui->tab3Text->setAlignment(Qt::AlignLeft | Qt::AlignTop);
    ui->tab1Text->setStyleSheet("padding: 5px;");
    ui->tab2Text->setStyleSheet("padding: 5px;");
    ui->tab3Text->setStyleSheet("padding: 5px;");
    ui->tab1Text->setText(this->getTextFromFile(":/textFiles/about.txt"));
    ui->tab2Text->setText(this->getTextFromFile(":/textFiles/contact.txt"));
    ui->tab3Text->setText(this->getTextFromFile(":/textFiles/termsOfService.txt"));

    this->registerAllBlockFiles();
}

DMPTPC::~DMPTPC()
{
    delete ui;
}

void DMPTPC::on_open_tp_button_clicked()
{
    QString zipPath = QFileDialog::getOpenFileName(this, tr("Open Texture Pack"), "C:/", tr("Zip File (*.zip)"));
    this->startConversionProcess(zipPath.toStdString());
}

void DMPTPC::startConversionProcess(const std::string &zipPath){
    //todo
}

void DMPTPC::registerAllBlockFiles(){
    this->registerBlockFileName("anvil_base", "anvil");
    this->registerBlockFileName("beacon", "beacon");
    this->registerBlockFileName("bedrock", "bedrock");

    //alot more of files to register
}

bool DMPTPC::registerBlockFileName(const std::string &key, const std::string &val){
    if(!blockFileNameList.count(key)){
        blockFileNameList[key] = val;
        return true;
    }
    return false;
}

void DMPTPC::console(const QString &log){
    //todo
}

QString DMPTPC::getTextFromFile(const QString &file){
    QFile f(file);
    QString output;
    if(f.open(QIODevice::ReadOnly)){
        QTextStream in(&f);
        output = in.readAll();
        f.close();
    }
    return output;
}

void DMPTPC::on_close_clicked()
{
    QCoreApplication::quit();
}

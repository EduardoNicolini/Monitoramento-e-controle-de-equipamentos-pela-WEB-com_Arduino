/*
 * ESTA VERSÃO ENVIA DADOS DOS SENSORES E RECEBE COMANDOS PARA ACIONAR PORTAS DO ARDUINO
 * 
 * POR MEIO DE SCRIPTS EM UM SERVIDOR WEB EXTERNO POR MEIO DE REQUISIÇÕES AJAX
*/

#include <SPI.h>
#include <Ethernet.h>
#include "EmonLib.h"
#include <LiquidCrystal.h>
#include <Timer.h>

Timer t; // Conficuração do tempo de Update com a WEB

byte mac[] = {0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED}; // Numero MAC do meu arduino

IPAddress ip(192,168,0,250); // Configura o IP do arduino com o roteador
IPAddress gateway(192,168,0,1); 
IPAddress subnet(255,255,255,0);

EthernetServer server(80);// Porta de comunicação com o servidor

// endereço do servidor onde vai estar instalado o wampserver 
byte servidor[] = { 192, 168, 0, 16 };

#define portaHTTP 80
#define heart 13

void Sending_data();

void monitor_lcd(float amper,float potencia, float kwh, float y);

EthernetClient cliente;

EnergyMonitor emon1; 

LiquidCrystal lcd(8, 7, 5, 4, 3, 2);  //Assign LCD screen pins

int rede = 127.0; // Recebe o valor de ID do botão na pagina no servidor e retorna em JSON para tomar a ação 

int analogPin1 = A0; // Porta de entrada analogica onde está conectado meu sensor de carga 

int rele = 9; // Porta digital onde está conectado meu Rele.

/*******************************************************/
  // TESTE COM VALORES EM REAIS A PAGAR POR Kwh
  unsigned long ltmillis, tmillis, timems, previousMillis, refresh;
  
  float Tempo = 0;
  
  float x = 0; //  Armazena o valor da conversão em reais R$
  float y;

/*******************************************************/

void setup(){
  
  Serial.begin(9600); // Configura o valor de comunicação com a porta serial
  
  lcd.begin(16, 4);// Define a configuração do tamanho do LCD usado
  lcd.clear(); // Linpa as linhas dimpressas no LCD
  
/*******************************************************/
  // TESTE COM VALORES EM REAIS A PAGAR POR Kwh

  previousMillis = millis();
/*******************************************************/
  
  Serial.println("Iniciando programa..");
  Ethernet.begin(mac,ip,gateway,subnet);
  Serial.println("Server ativo no IP: ");
  
  Serial.print(Ethernet.localIP()); // Imprime o valor do IP no monitor serial
  
  server.begin();
  
   //Pino, calibracao - Cur Const= Ratio/BurdenR. 1800/62 = 29.
  emon1.current(analogPin1, 29);
  
  pinMode(rele, OUTPUT); // Configura se a porta usada é de saida ou de entrada

  digitalWrite(rele, HIGH); // Inicia a porta de saida em nivel alto 1

  // ConfiguraçÕes de Tempo de atualização dos dados para o servidor servidor MySql
  pinMode(heart, OUTPUT);
  delay(2000);
  
  t.oscillate(heart, 1000, LOW);

  // Tempo de atualização dos dados para o servidor MySql
  //O valor de (60000, Sending_data) equivale a 1 minuto.(1800000) equivale a 30 minutos.(3600000) equivale a 60 minutos.
  t.every(30000, Sending_data); 
  
}

void loop(){
  
  /*******************************************************/
  // TESTE COM VALORES EM REAIS A PAGAR POR Kwh

  // Calcula quantidade de tempo desde a última measurment realpower.
    ltmillis = tmillis;
    tmillis = millis();
    timems = tmillis - ltmillis;

/*******************************************************/

  t.update();//Inicia a contagem de tempo de atualização de dados para o thingspeak em  Void Setup ()

  /* Configuração do sensor  */
  
  // Configuração e calibragem do sensor de carga  //
    double Irms = emon1.calcIrms(1480);
  
    if (Irms < 0.15){
        Irms = 0;
    }

    float amper = (Irms);
    
    float potencia = (Irms * rede);  //Irms * rede, 1
   

  /**************************************************/
    // Converte para KWh
    float kwh = (Irms*rede*(Tempo/3600));//3600000 
    
    Tempo++;
    
 /**************************************************/

    //Equação para obtenção do valor a pagar em reais R$
   x = ((kwh) * 0.0002778) * 0.49231;
   
   y = y + x;

  /**************************************************/
    // Envia comando para void monitor_lcd
     monitor_lcd(amper, potencia, kwh, y);
    
 /**************************************************/
 /*  ABAIXO O CODIGO RECEBE E ENVIA DADOS POR MEIO DE REQUISIÇÕES DE UM SERVIDOR EXTERNO VIA AJAX  */
 /**************************************************/
 
    // Abre a conexão com o servidor
    EthernetClient client = server.available();
    
    if(client){
      
        boolean continua = true;
        
        String linha = "";
  
        while(client.connected()){
          
            if(client.available()){
              
                char c = client.read();
                
                linha.concat(c);
        
                if(c == '\n' && continua){
                  
                    client.println("HTTP/1.1 200 OK");
                    
                    // ISSO FAZ O ARDUINO RECEBER REQUISIÇÃO AJAX DE OUTRO SERVIDOR E NÃO APENAS LOCAL.
                    client.println("Content-Type: text/javascript");
                    client.println("Access-Control-Allow-Origin: *");
                    client.println();          
                   
                    int iniciofrente = linha.indexOf("?");
                          
                    if(iniciofrente>-1){  //verifica se o comando veio
                      
                        iniciofrente = iniciofrente+6; //pega o caractere seguinte,
                        
                        int fimfrente = iniciofrente+3; //esse comando espero 3 caracteres
                        
                        String acao = linha.substring(iniciofrente,fimfrente);//recupero o valor do comando
             
                        if (acao == "001"){ 
                            digitalWrite(rele, LOW); // Recebe o valor de ID do botão na pagina no servidor e retorna em JSON para tomar a ação 
                        } 
                        
                        else if (acao == "002"){ 
                            digitalWrite(rele, HIGH); // Recebe o valor de ID do botão na pagina no servidor e retorna em JSON para tomar a ação 
                        } 
               
                        client.print("dados({ Irms : "); // Envia os valores do sensor e de estatos do rele em JSON para o servidor
                        client.print(Irms); // Passa o valor da corrente eletrica em uma variavel
                        client.print(", potencia :  ");
                        client.print(potencia); // Passa o valor da potencia eletrica em uma variavel
                        client.print(",");
                        client.print(" rele : ");
                        client.print(digitalRead(rele)); // Passa o valor de status do rele em uma variavel
                        client.print("})");
                        
                    }          
                    break;
                }
          
                if(c == '\n'){ 
                    continua = true;
                }
                
                else if (c != '\r'){ 
                    continua = false; 
                }
            }
        }
        delay(1);
        
        client.stop();
    } 
}

// ENVIA DADOS DOS SENSOEES VIA GET PARA SALVAR EM MySql EM UM SERVIDOR EXTERNO 
void Sending_data(){ 
   
  // Configuração e calibragem do sensor de carga  //
    double Irms = emon1.calcIrms(1480);
  
    if (Irms < 0.15){
        Irms = 0;
    }

    float amper = (Irms);
    
    float potencia = (Irms * rede);  //Irms * rede, 1

 /**************************************************/
    // Converte para KWh
    float kwh = (Irms * rede * (Tempo/3600));//3600000 
    
    Tempo++;
    
 /**************************************************/
    
    if(potencia > 0){
          
        if(cliente.connect(servidor, portaHTTP)){
    
            //clienteArduino.println("GET /arduino/teste.php HTTP/1.0");
            cliente.print("GET /ajax/versao_12/salva_bd.php");
            cliente.print("?irms=");
            cliente.print(amper);
            cliente.print("&potencia=");
            cliente.print(potencia);
            cliente.print("&kwh=");
            cliente.print(kwh);
                
            cliente.println(" HTTP/1.0");
            cliente.println("Host: 192.168.0.16");
            cliente.println("Connection: close");
            cliente.println();
        } 
        else{
            Serial.println("Falha ao conectar com o servidor");         
        }
    }
}

void monitor_lcd(float amper,float potencia,float kwh, float y){
  
    //Imprime o valor da corrente eletrica no display LCD
    lcd.setCursor(0, 0);
    lcd.print("Corr.(A):");
    lcd.setCursor(10, 0);
    lcd.print(amper);
   
    //Imprime o valor da potencia no monitor serial
    lcd.setCursor(0, 1);
    lcd.print("Pot.(W):");
    lcd.setCursor(10, 1);
    lcd.print(potencia);

    lcd.setCursor(0, 2);
    lcd.print("kwh:");
    lcd.setCursor(10, 2);
    lcd.print(kwh);

    lcd.setCursor(0, 3);
    lcd.print("R$:");
    lcd.setCursor(10, 3);
    lcd.print(y);
    delay(1000);
}

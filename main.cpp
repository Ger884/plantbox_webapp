#include <iostream>
#include <mysql.h> // หากยังแดง ให้ลองเปลี่ยนเป็น #include <mysql.h> หลังทำข้อ 1
#include <fcntl.h>        
#include <termios.h>      
#include <unistd.h>       
#include <string>
#include <cstring>

using namespace std;

int main() {
    // --- 1. Database Connection ---
    MYSQL *conn = mysql_init(NULL);
    if (!mysql_real_connect(conn, "localhost", "root", "your_password", "plantbox", 0, NULL, 0)) {
        cerr << "Database Connection Error: " << mysql_error(conn) << endl;
        return 1;
    }
    cout << "Successfully connected to MariaDB!" << endl;

    // --- 2. Serial Port Setup ---
    // ตรวจสอบพอร์ตด้วยคำสั่ง ls /dev/tty* ใน terminal
    int serial_port = open("/dev/ttyUSB0", O_RDWR); 
    if (serial_port < 0) {
        perror("Unable to open serial port");
        return 1;
    }

    struct termios tty;
    if (tcgetattr(serial_port, &tty) != 0) {
        perror("Error from tcgetattr");
        return 1;
    }

    cfsetispeed(&tty, B115200);
    cfsetospeed(&tty, B115200);

    tty.c_cflag |= (CLOCAL | CREAD);    // เชื่อมต่อและอ่านข้อมูลได้
    tty.c_cflag &= ~CSIZE;
    tty.c_cflag |= CS8;                 // 8-bit characters
    tty.c_cflag &= ~PARENB;             // No parity bit
    tty.c_cflag &= ~CSTOPB;             // 1 stop bit

    tcsetattr(serial_port, TCSANOW, &tty);

    // --- 3. Main Loop ---
    char buf[256];
    cout << "System running... Watching for ESP32 data." << endl;

    while (true) {
        int n = read(serial_port, buf, sizeof(buf) - 1);
        if (n > 0) {
            buf[n] = '\0';
            string data(buf);
            
            // ลบ whitespace/newline
            data.erase(data.find_last_not_of(" \n\r\t") + 1);
            
            if (data.empty()) continue;

            cout << "Received: " << data << endl;

            string query = "INSERT INTO soil_data (temp, ec, hum, ph, n, p, k) VALUES (" + data + ")";
            
            if (mysql_query(conn, query.c_str())) {
                cerr << "Query Error: " << mysql_error(conn) << endl;
            } else {
                cout << ">> Data stored in DB." << endl;
            }
        }
        usleep(100000); // หน่วงเวลา 0.1 วินาที
    }

    mysql_close(conn);
    return 0;
}
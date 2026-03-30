-- =================================================================================
-- Database Schema & Sample Data for Pornsiri Farm IoT Dashboard (MySQL / MariaDB)
-- =================================================================================

-- 1. สร้างฐานข้อมูลและกำหนด Charset รองรับภาษาไทย
CREATE DATABASE IF NOT EXISTS pornsiri_farm_iot CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pornsiri_farm_iot;

-- 2. ลบตารางเก่าทิ้งถ้ามี (เพื่อป้องกัน Error หากรันซ้ำ)
DROP TABLE IF EXISTS sensor_log;
DROP TABLE IF EXISTS power_log;
DROP TABLE IF EXISTS alerts;
DROP TABLE IF EXISTS system_settings;
DROP TABLE IF EXISTS nodes;

-- ---------------------------------------------------------------------------------
-- Table: nodes (ข้อมูลบอร์ด ESP32)
-- ---------------------------------------------------------------------------------
CREATE TABLE nodes (
    node_id VARCHAR(20) PRIMARY KEY,
    location_name VARCHAR(100) NOT NULL,
    ip_address VARCHAR(15),
    mac_address VARCHAR(20),
    firmware_ver VARCHAR(20),
    status VARCHAR(20) DEFAULT 'online', -- online, warn, offline
    battery_pct INT DEFAULT 100,
    rssi INT DEFAULT -50,
    uptime_str VARCHAR(50),
    is_pump_on TINYINT(1) DEFAULT 0,
    is_lamp_on TINYINT(1) DEFAULT 0,
    last_sync TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO nodes (node_id, location_name, ip_address, mac_address, firmware_ver, status, battery_pct, rssi, uptime_str, is_pump_on, is_lamp_on)
VALUES 
('Node-01', 'แปลง A (โรงเรือน 1)', '192.168.1.101', '24:0A:C4:00:01:1A', 'v2.5.1', 'online', 92, -48, '14d 05h 12m', 0, 0),
('Node-02', 'แปลง B (กลางแจ้ง)', '192.168.1.102', '24:0A:C4:00:02:2B', 'v2.5.1', 'online', 78, -62, '14d 02h 45m', 0, 0),
('Node-03', 'แปลง C (มุมอับ)', '192.168.1.103', '24:0A:C4:00:03:3C', 'v2.5.0', 'warn', 18, -75, '05d 12h 30m', 0, 1),
('Node-04', 'แปลง D (โรงเรือน 2)', '192.168.1.104', '24:0A:C4:00:04:4D', 'v2.5.1', 'online', 65, -55, '22d 08h 15m', 0, 0),
('Node-05', 'แปลง E (ทางเข้า)', '192.168.1.105', '24:0A:C4:00:05:5E', 'v2.5.1', 'online', 88, -43, '30d 10h 22m', 1, 0);

-- ---------------------------------------------------------------------------------
-- Table: sensor_log (เก็บประวัติค่าเซ็นเซอร์ทั้งหมด)
-- ---------------------------------------------------------------------------------
CREATE TABLE sensor_log (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    node_id VARCHAR(20),
    temp DECIMAL(5,2),
    humi DECIMAL(5,2),
    soil_moisture DECIMAL(5,2),
    light_lux INT,
    uv_index DECIMAL(4,2),
    pm25 DECIMAL(5,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (node_id) REFERENCES nodes(node_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ข้อมูลจำลองย้อนหลังของ Node-01 (เพื่อใช้วาดกราฟ)
INSERT INTO sensor_log (node_id, temp, humi, soil_moisture, light_lux, uv_index, pm25, created_at)
VALUES 
('Node-01', 26.2, 68.0, 52.1, 0, 0.0, 8.2, DATE_SUB(NOW(), INTERVAL 5 HOUR)),
('Node-01', 25.8, 70.0, 50.4, 0, 0.0, 7.8, DATE_SUB(NOW(), INTERVAL 4 HOUR)),
('Node-01', 28.1, 63.0, 48.2, 2500, 1.2, 12.1, DATE_SUB(NOW(), INTERVAL 3 HOUR)),
('Node-01', 30.6, 56.0, 45.1, 7200, 5.8, 16.2, DATE_SUB(NOW(), INTERVAL 2 HOUR)),
('Node-01', 31.8, 52.0, 41.2, 9800, 8.8, 20.1, DATE_SUB(NOW(), INTERVAL 1 HOUR)),
('Node-01', 28.4, 62.1, 45.2, 4500, 4.2, 12.4, NOW()),

-- ข้อมูลปัจจุบันของ Node อื่นๆ
('Node-02', 31.2, 55.0, 32.5, 8500, 7.5, 24.1, NOW()),
('Node-03', 29.5, 68.4, 55.0, 850, 1.2, 8.6, NOW()),
('Node-04', 27.8, 65.2, 48.7, 3200, 3.5, 18.9, NOW()),
('Node-05', 30.1, 58.6, 28.1, 6200, 5.8, 11.2, NOW());

-- ---------------------------------------------------------------------------------
-- Table: power_log (ข้อมูลการใช้พลังงานไฟฟ้า PZEM-004T)
-- ---------------------------------------------------------------------------------
CREATE TABLE power_log (
    pwr_id INT AUTO_INCREMENT PRIMARY KEY,
    equipment_name VARCHAR(100) NOT NULL,
    voltage DECIMAL(5,2),
    current_amp DECIMAL(5,2),
    active_power_w DECIMAL(8,2),
    power_factor DECIMAL(3,2),
    energy_kwh DECIMAL(10,2),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO power_log (equipment_name, voltage, current_amp, active_power_w, power_factor, energy_kwh, is_active)
VALUES 
('Water Pump (แปลง A-B)', 220.5, 5.20, 974.6, 0.85, 45.2, 1),
('Grow Lights (โรงเรือน)', 220.5, 0.10, 21.0, 0.95, 78.5, 0),
('Ventilation Fans', 220.5, 3.80, 754.1, 0.90, 12.1, 1),
('IoT Control Cabinet', 220.5, 0.80, 172.8, 0.98, 6.7, 1);

-- ---------------------------------------------------------------------------------
-- Table: alerts (ระบบแจ้งเตือน)
-- ---------------------------------------------------------------------------------
CREATE TABLE alerts (
    alert_id INT AUTO_INCREMENT PRIMARY KEY,
    node_id VARCHAR(20),
    alert_type VARCHAR(20) NOT NULL, -- critical, warning, info
    title VARCHAR(150) NOT NULL,
    description TEXT,
    is_resolved TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO alerts (node_id, alert_type, title, description, is_resolved, created_at)
VALUES 
('Node-04', 'critical', 'PM2.5 ทะลุขีดจำกัด (Hazardous)', 'ค่าฝุ่น PM2.5 พุ่งสูงถึง 158 µg/m³ เกินค่าความปลอดภัยที่ตั้งไว้', 0, DATE_SUB(NOW(), INTERVAL 10 MINUTE)),
('Node-03', 'warning', 'Battery Low Alert', 'ระดับแบตเตอรี่ของ Node-03 ลดลงเหลือ 18% กรุณาตรวจสอบแผงโซลาร์เซลล์', 0, DATE_SUB(NOW(), INTERVAL 1 HOUR)),
('Node-01', 'critical', 'High Temperature Detected', 'อุณหภูมิในโรงเรือนสูงถึง 36.5 °C เสี่ยงต่อความเสียหายของพืช', 0, DATE_SUB(NOW(), INTERVAL 2 HOUR)),
('System', 'info', 'Database Backup Completed', 'สำรองข้อมูลไปยังเซิร์ฟเวอร์เรียบร้อยแล้ว (Size: 142.5 MB)', 1, DATE_SUB(NOW(), INTERVAL 1 DAY)),
('Node-02', 'warning', 'Soil Moisture Dropped', 'ความชื้นในดินลดลงเหลือ 28% ระบบรดน้ำอัตโนมัติทำงานแล้ว', 1, DATE_SUB(NOW(), INTERVAL 1 DAY));

-- ---------------------------------------------------------------------------------
-- Table: system_settings (การตั้งค่าระบบ)
-- ---------------------------------------------------------------------------------
CREATE TABLE system_settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT,
    description VARCHAR(200)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO system_settings (setting_key, setting_value, description)
VALUES 
('farm_name', 'พรศิริฟาร์มสุข (Pornsiri Farm Sook)', 'ชื่อโปรเจกต์หรือชื่อฟาร์ม'),
('auto_irrigation', '1', 'เปิด/ปิด ระบบรดน้ำอัตโนมัติ (1=เปิด, 0=ปิด)'),
('auto_grow_light', '1', 'เปิด/ปิด ระบบไฟปลูกพืชอัตโนมัติ'),
('threshold_soil_min', '30.0', 'ค่าความชื้นดินต่ำสุดที่จะสั่งรดน้ำ (%)'),
('threshold_light_min', '1000', 'ค่าความสว่างต่ำสุดที่จะสั่งเปิดไฟ (Lux)'),
('alert_temp_max', '35.0', 'แจ้งเตือนเมื่ออุณหภูมิสูงเกินกำหนด (°C)'),
('alert_pm25_max', '55.0', 'แจ้งเตือนเมื่อฝุ่น PM2.5 สูงเกินกำหนด (µg/m³)');

-- =================================================================================
-- END OF SCRIPT
-- =================================================================================
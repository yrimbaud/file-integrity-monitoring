# File Integrity Monitoring

## Presentation

File Integrity Monitoring is a PHP-based tool designed to track file changes on a server. It monitors file sizes and MD5 hashes to detect modifications, providing an easy-to-use web interface for viewing and managing file changes.

The tool maintains a history of file modifications in a SQLite database, allowing users to track changes over time. When files are modified, it displays both the previous and current sizes, making it easy to identify significant changes.

## Prerequisites
- PHP 7.4 or higher
- SQLite3 PHP extension
- Write permissions in the directory for SQLite database

## Installation
1. Clone this repository to your local machine.
2. Navigate to your web server directory.
3. Copy the `scan.php` file to your desired location.
4. Ensure PHP has write permissions in the directory for creating the SQLite database.
5. Access the script through your web browser.

## How to Use
Access the `scan.php` file through your web browser. The interface provides the following features:
- Start Scan: Initiates a new scan of all files in the specified directory
- File List: Displays all monitored files with their current sizes and last scan dates
- Modified Files: Shows files that have changed, including:
  - Previous file size
  - Current file size
  - Size difference
- Hide Files: Select files you don't want to monitor anymore and hide them from the list

The script automatically tracks:
- File paths
- File sizes
- MD5 hashes
- Modification timestamps

## Supporting The Project

If you find this project beneficial and appreciate its contributions, you might consider offering your support. One of the ways you can do this is through a Bitcoin donation!

Here is the Bitcoin address:
`bc1q3pc0ftvdew3e87k07d00k8tqj7ll924hgy69n6`

By donating Bitcoin, you are not only providing tangible assistance, but also endorsing the use of decentralized digital currencies. This encourages further innovation and freedom in the financial sector, aligning with the open source principles that guide this project.

Every donation, big or small, is deeply appreciated and will be used to further improve and maintain this project. Your support helps dedicate more time and resources, ensuring the project's continuity and enhancement!

## Author

This project is maintained by Yann Rimbaud ([yrimbaud](https://github.com/yrimbaud)).

## Licence

This project is licensed under the MIT License.

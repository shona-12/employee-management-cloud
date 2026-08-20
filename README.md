Employee Management Cloud

Production-Style AWS Infrastructure • Terraform • Ansible • PHP • MySQL

A fully automated employee management application deployed on AWS using Infrastructure as Code and configuration management.

Built to demonstrate how modern DevOps practices can be used to provision infrastructure, configure servers, deploy applications, secure network communication, and connect application workloads to a managed database.

---

What I Built

This project takes an application from source code → cloud infrastructure → automated deployment → live application.

The environment is provisioned using Terraform and configured using Ansible, with the application running on multiple EC2 instances behind an Application Load Balancer.

Core Architecture

                         ┌──────────────────────┐
                         │       INTERNET       │
                         └──────────┬───────────┘
                                    │
                                    ▼
                         ┌──────────────────────┐
                         │  Application Load    │
                         │      Balancer        │
                         └──────────┬───────────┘
                                    │
                         ┌──────────┴───────────┐
                         │                      │
                         ▼                      ▼
                  ┌──────────────┐       ┌──────────────┐
                  │   EC2 App 1  │       │   EC2 App 2  │
                  │ Apache + PHP │       │ Apache + PHP │
                  └──────┬───────┘       └──────┬───────┘
                         │                      │
                         └──────────┬───────────┘
                                    │
                                    ▼
                           ┌────────────────┐
                           │   Amazon RDS   │
                           │   MySQL        │
                           └────────────────┘

---

Architecture at a Glance

The application follows a three-tier AWS architecture designed around separation of concerns, network isolation, scalability, and controlled access.

Layer| AWS Components| Responsibility
Presentation| Application Load Balancer| Public entry point and traffic distribution
Application| EC2, Auto Scaling, Apache, PHP| Application processing and business logic
Data| Amazon RDS MySQL| Persistent employee data storage
Security| IAM, Security Groups, Private Subnets| Access control and network isolation
Networking| VPC, Route Tables, IGW, NAT Gateway| Secure and controlled network connectivity

Request Flow

                         ┌──────────────────────┐
                         │       INTERNET       │
                         └──────────┬───────────┘
                                    │
                                    ▼
                         ┌──────────────────────┐
                         │  APPLICATION LOAD    │
                         │      BALANCER        │
                         └──────────┬───────────┘
                                    │
                         ┌──────────┴───────────┐
                         │                      │
                         ▼                      ▼
                  ┌──────────────┐       ┌──────────────┐
                  │   EC2 App 1  │       │   EC2 App 2  │
                  │ Apache + PHP │       │ Apache + PHP │
                  └──────┬───────┘       └──────┬───────┘
                         │                      │
                         └──────────┬───────────┘
                                    │
                                    ▼
                           ┌────────────────┐
                           │   Amazon RDS   │
                           │   MySQL        │
                           └────────────────┘

Network Design

                         AWS VPC
                            │
              ┌─────────────┴─────────────┐
              │                           │
        Public Subnets              Private Subnets
              │                           │
         ┌────┴────┐              ┌───────┴────────┐
         │   ALB   │              │                │
         └─────────┘          Application       Database
                               EC2 / ASG           RDS
                                  │                │
                                  └───────┬────────┘
                                          │
                                     NAT Gateway
                                  (Outbound Access)

Design Principles

- Separation of Tiers: Presentation, application, and database layers have distinct responsibilities.
- High Availability: Application instances are distributed across multiple Availability Zones.
- Controlled Connectivity: Network traffic is permitted only where required between infrastructure layers.
- Scalable Compute: Auto Scaling enables the application tier to adapt to changing demand.
- Automated Infrastructure: Terraform and Ansible reduce manual configuration and improve consistency.
- Reproducible Deployment: Infrastructure and deployment configuration are maintained as version-controlled code.

---

Infrastructure

The complete AWS environment is provisioned through Terraform, following an Infrastructure as Code approach instead of manually creating resources through the AWS Console.

Terraform provisions

VPC
├── Public Subnets
│   ├── Internet Gateway
│   └── Application Load Balancer
│
├── Private Application Subnets
│   ├── EC2
│   └── Auto Scaling Group
│
├── Private Database Subnets
│   └── Amazon RDS MySQL
│
├── NAT Gateway
├── Route Tables
├── Security Groups
└── Bastion Host

Provisioning Workflow

terraform init
terraform validate
terraform plan
terraform apply

Destroy Infrastructure

When the environment is no longer required:

terraform destroy

---

Automated Deployment

After infrastructure provisioning, Ansible handles server configuration and application deployment.

The playbook automates:

- Apache installation
- PHP installation
- Apache service management
- Application file deployment
- Database configuration
- Environment configuration
- Default Apache page removal
- Database initialization
- Deployment validation

Deployment

cd ansible
ansible-playbook site.yml

Successful deployment produces:

Database setup successful

---

Security Architecture

The infrastructure follows a layered security model based on network isolation, controlled communication, and least-privilege principles.

Internet-Facing Layer

Internet
   │
   ▼
Application Load Balancer

Application Layer

ALB
 │
 ▼
Private EC2

Database Layer

Private EC2
     │
     ▼
Private RDS

Security Controls

- Private Database: RDS is isolated from direct public internet access.
- Network Segmentation: Public, application, and database resources are separated into appropriate subnet tiers.
- Traffic Control: Security Groups restrict inbound and outbound communication between required components.
- Access Management: IAM controls access to AWS resources according to required permissions.
- Credential Protection: Database credentials are provided through environment variables rather than application source code.
- Controlled Egress: Private resources use NAT Gateway for required outbound internet connectivity.

---

Application & Database

The application stack consists of:

PHP
 │
 ▼
Apache Web Server
 │
 ▼
EC2 Application Server
 │
 ▼
Amazon RDS MySQL

The PHP application connects to the managed MySQL database hosted by Amazon RDS.

Database configuration is supplied through environment variables:

DB_HOST
DB_USER
DB_PASSWORD
DB_NAME

This keeps database credentials outside the application source code.

---

DevOps Pipeline

                 ┌──────────────┐
                 │    GitHub    │
                 └──────┬───────┘
                        │
                        ▼
                 ┌──────────────┐
                 │   Terraform  │
                 └──────┬───────┘
                        │
                        ▼
                 ┌────────────────────┐
                 │  AWS Infrastructure│
                 └─────────┬──────────┘
                           │
                           ▼
                 ┌──────────────┐
                 │    Ansible   │
                 └──────┬───────┘
                        │
                        ▼
                 ┌──────────────┐
                 │  EC2 Servers │
                 └──────┬───────┘
                        │
                        ▼
                 ┌──────────────┐
                 │  PHP + Apache│
                 └──────┬───────┘
                        │
                        ▼
                 ┌──────────────┐
                 │   RDS MySQL  │
                 └──────────────┘

The workflow demonstrates an automated infrastructure and deployment lifecycle:

Code → Provision → Configure → Deploy → Validate

---

Repository Structure

employee-management-cloud/
│
├── main.tf
├── provider.tf
├── variables.tf
├── outputs.tf
│
├── app/
│   ├── index.php
│   ├── add_employee.php
│   ├── db.php
│   └── setup.php
│
├── ansible/
│   ├── inventory
│   ├── site.yml
│   └── app/
│       ├── index.php
│       ├── add_employee.php
│       ├── db.php
│       └── setup.php
│
└── README.md

---

Technology Stack

AWS

"EC2" "VPC" "RDS" "Application Load Balancer" "Auto Scaling" "IAM" "EBS" "NAT Gateway" "Internet Gateway" "Security Groups" "Route Tables" "Elastic IP"

DevOps & Automation

"Terraform" "Ansible" "Infrastructure as Code" "Configuration Management" "Deployment Automation" "Infrastructure Provisioning"

Linux

"Ubuntu" "SSH" "Bash" "Apache" "Package Management" "File Permissions" "Service Management" "Linux Administration"

Application

"PHP" "MySQL" "HTML" "CSS" "Apache"

Version Control

"Git" "GitHub" "Git Workflows" "Branching" "Commits" "Repository Management"

---

Engineering Skills Demonstrated

Cloud Architecture

- AWS infrastructure design
- Multi-AZ architecture
- High availability concepts
- Scalable application architecture
- Managed database architecture
- Cloud resource lifecycle management

Infrastructure as Code

- Terraform
- Infrastructure provisioning
- Terraform state management
- Resource dependencies
- Infrastructure planning
- Reproducible environments
- Automated infrastructure lifecycle

Configuration Management

- Ansible
- Inventory management
- Automated server configuration
- Application deployment
- Idempotent configuration
- Remote server management

Networking

- VPC architecture
- CIDR and subnetting
- Public and private subnet design
- Route tables
- Internet Gateway
- NAT Gateway
- Application Load Balancer
- Network segmentation
- Multi-AZ networking

Security

- IAM fundamentals
- Security Groups
- Least-privilege principles
- Private database architecture
- Network isolation
- Credential management
- Secure application-to-database communication

Linux Administration

- Ubuntu
- SSH
- Bash/Shell
- Apache
- Package management
- Service management
- File permissions
- Linux troubleshooting
- Process and system management

Database

- MySQL
- Amazon RDS
- Database connectivity
- SQL fundamentals
- Application-to-database architecture
- Database initialization

DevOps

- Infrastructure automation
- Configuration automation
- Deployment automation
- Cloud infrastructure management
- Git-based workflows
- Environment reproducibility
- Infrastructure lifecycle management

Version Control

- Git
- GitHub
- Repository management
- Branch management
- Commit management
- Remote repositories
- Version-controlled infrastructure

---

Validation

Terraform Validation

terraform validate
terraform plan

Infrastructure Deployment

terraform apply

Ansible Deployment

ansible-playbook site.yml

Database Connectivity

Application
     │
     ▼
Private EC2
     │
     ▼
Amazon RDS MySQL

Application Traffic

User
 │
 ▼
Application Load Balancer
 │
 ▼
EC2 Application Servers
 │
 ▼
PHP Application
 │
 ▼
Amazon RDS MySQL

The infrastructure, configuration management, database connectivity, and application deployment were validated successfully during the project.

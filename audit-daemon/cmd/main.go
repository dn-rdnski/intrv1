package main

import (
	"auditdaemon/service"
	"flag"
	"log"
	"os"
	"os/signal"
)

func main() {

	socketAddr := flag.String("socket", "/run/audit-daemon.sock", "Socket address for listening")
	flag.Parse()

	audit := service.NewAuditListener(*socketAddr)

	signalChannel := make(chan os.Signal, 1)
	signal.Notify(signalChannel)

	err := audit.Run()
	if err != nil {
		log.Println("ERROR: cannot start audit listener: ", err)
		os.Exit(1)
	}

	sig := <-signalChannel
	log.Printf("shutdown, received signal: %s", sig)
}

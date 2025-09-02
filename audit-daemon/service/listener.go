package service

import (
	"bytes"
	"io"
	"log/slog"
	"net"
	"os"
)

type AuditListener struct {
	SocketPath string
	Logger     *slog.Logger
}

func NewAuditListener(socketPath string) *AuditListener {
	return &AuditListener{
		SocketPath: socketPath,
		Logger:     slog.Default(),
	}
}

func (als *AuditListener) Run() error {

	_ = os.Remove(als.SocketPath)
	listener, err := net.Listen("unix", als.SocketPath)
	if err != nil {
		return err
	}
	als.Logger.Info("stared listener", "socket_path", als.SocketPath)

	go func() {
		for {
			conn, err := listener.Accept()
			if err != nil {
				als.Logger.Error("error accepting connection", "error", err)
			}
			go als.HandleConnection(conn)
		}
	}()

	return nil
}

func (als *AuditListener) HandleConnection(connection net.Conn) {
	data, err := io.ReadAll(connection)
	if err != nil {
		als.Logger.Error("error reading from connection", "error", err)
		return
	}

	als.Logger.Info("received event", "data", string(bytes.TrimSpace(data)))
}

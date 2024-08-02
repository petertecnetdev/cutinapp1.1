import React, { useState, useEffect } from "react";
import { Button, Card, Col, Container, Row, Modal, Alert } from "react-bootstrap";
import { Link } from "react-router-dom";
import userService from "../../../services/UserService";
import NavlogComponent from "../../../components/NavlogComponent";
import { storageUrl } from "../../../config";

const UserListPage = () => {
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [showConfirmModal, setShowConfirmModal] = useState(false);
  const [selectedUserId, setSelectedUserId] = useState(null);
  const [showSuccessAlert, setShowSuccessAlert] = useState(false);
  const [showErrorAlert, setShowErrorAlert] = useState(false);
  const [modalErrorMessage, setModalErrorMessage] = useState("");

  // Simulação do ID do usuário autenticado. Substitua isso pelo ID real do usuário autenticado
  const authenticatedUserId = 1; // Substitua pelo ID real do usuário autenticado

  
  const fetchUsers = async () => {
    setLoading(true);
    setError(null);
    try {
      const response = await userService.list();
      setUsers(response.users || []);
    } catch (error) {
      console.error("Erro ao obter a lista de usuários:", error);
      setError("Erro ao obter a lista de usuários. Por favor, tente novamente.");
    } finally {
      setLoading(false);
    }
  };

  const handleDeleteUser = async (id) => {
    try {
      await userService.destroy(id);
      fetchUsers();
      setShowSuccessAlert(true);
      setTimeout(() => setShowSuccessAlert(false), 5000);
    } catch (error) {
      if (error.response && error.response.data && error.response.data.error) {
        setModalErrorMessage(error.response.data.error);
      } else {
        setModalErrorMessage("Não foi possível deletar o usuário");
      }
      setShowConfirmModal(true); // Exibe o modal com a mensagem de erro
    }
  };

  const handleConfirmDelete = () => {
    if (selectedUserId === authenticatedUserId) {
      setModalErrorMessage("Você não pode se auto-deletar.");
      // Não fecha o modal, apenas exibe a mensagem de erro
    } else {
      handleDeleteUser(selectedUserId);
      setShowConfirmModal(false);
    }
  };

  const handleCloseModal = () => {
    setShowConfirmModal(false);
    setSelectedUserId(null);
    setModalErrorMessage("");  // Clear error message when closing the modal
  };

  const handleShowConfirmModal = (id) => {
    setSelectedUserId(id);
    setShowConfirmModal(true);
  };

  useEffect(() => {
    fetchUsers();
  }, []);

  return (
    <>
      <NavlogComponent />
      <Container>
        <Row className="justify-content-md-center">
          <Col md={12} className="mt-5">
            <h1>Lista de Usuários</h1>
            {loading && <p>Carregando...</p>}
            {error && (
              <Alert
                variant="danger"
                style={{
                  position: "fixed",
                  top: "10px",
                  right: "10px",
                  zIndex: "1000",
                }}
              >
                {error}
              </Alert>
            )}
            {showSuccessAlert && (
              <Alert
                variant="success"
                style={{
                  position: "fixed",
                  top: "10px",
                  right: "10px",
                  zIndex: "1000",
                }}
                onClose={() => setShowSuccessAlert(false)}
                dismissible
              >
                Usuário deletado com sucesso
              </Alert>
            )}
            {showErrorAlert && (
              <Alert
                variant="danger"
                style={{
                  position: "fixed",
                  top: "10px",
                  right: "10px",
                  zIndex: "1000",
                }}
                onClose={() => setShowErrorAlert(false)}
                dismissible
              >
                Não foi possível deletar o usuário
              </Alert>
            )}
            <Row>
              {users.map((user) => (
                <Col key={user.id} md={4} className="mb-4">
                  <Card className="h-100 text-white bg-dark">
                    <Card.Img
                      variant="top"
                      src={
                        user.avatar
                          ? `${storageUrl}/${user.avatar}`
                          : "/images/loadingimage.gif"
                      }
                      alt="User Avatar"
                      style={{
                        borderRadius: "50%",
                        width: "50px",
                        height: "50px",
                        margin: "auto",
                        marginTop: "10px",
                      }}
                    />
                    <Card.Body className="d-flex flex-column">
                      <Card.Title>
                        {user.first_name} {user.last_name}
                      </Card.Title>
                      <Card.Text>{user.email}</Card.Text>
                      <Link to={`/user/${user.user_name}`} className="btn btn-primary mt-auto">
                        {user.user_name}
                      </Link>
                      <Button
                        variant="danger"
                        className={`mt-2 ${user.id === authenticatedUserId ? 'disabled' : ''}`}
                        onClick={() => user.id !== authenticatedUserId && handleShowConfirmModal(user.id)}
                        disabled={user.id === authenticatedUserId}
                      >
                        Deletar
                      </Button>
                    </Card.Body>
                  </Card>
                </Col>
              ))}
            </Row>
          </Col>
        </Row>
        <Link to="/user/create">
          <Button
            variant="primary"
            disabled={loading}
            style={{
              position: "fixed",
              bottom: "50px",
              right: "20px",
              zIndex: "1000",
            }}
          >
            {loading ? "Carregando..." : "Adicionar"}
          </Button>
        </Link>
      </Container>

      <Modal
        className="text-dark"
        show={showConfirmModal}
        onHide={handleCloseModal}
      >
        <Modal.Header closeButton>
          <Modal.Title>Confirmar exclusão</Modal.Title>
        </Modal.Header>
        <Modal.Body>
          {modalErrorMessage || "Deseja excluir este usuário? Isso será irreversível."}
        </Modal.Body>
        <Modal.Footer>
          <Button variant="secondary" onClick={handleCloseModal}>
            Cancelar
          </Button>
          <Button
            variant="danger"
            onClick={handleConfirmDelete}
          >
            Excluir
          </Button>
        </Modal.Footer>
      </Modal>
    </>
  );
};

export default UserListPage;

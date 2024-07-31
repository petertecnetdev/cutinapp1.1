import React, { useState, useEffect } from "react";
import { Button, Card, Col, Container, Row, Table, Modal, Alert } from "react-bootstrap"; // Adicionando o componente Alert
import { Link } from "react-router-dom";
import profileService from "../../../services/ProfileService";
import NavlogComponent from "../../../components/NavlogComponent";

const ProfileListPage = () => {
  const [profiles, setProfiles] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [showConfirmModal, setShowConfirmModal] = useState(false);
  const [selectedProfileId, setSelectedProfileId] = useState(null);
  const [showSuccessAlert, setShowSuccessAlert] = useState(false); // Estado para controlar a exibição do alerta de sucesso
  const [showErrorAlert, setShowErrorAlert] = useState(false); // Estado para controlar a exibição do alerta de erro

  const fetchProfiles = async () => {
    setLoading(true);
    setError(null);
    try {
      const response = await profileService.list();
      setProfiles(response);
    } catch (error) {
      console.error("Erro ao obter a lista de perfis:", error);
      setError("Erro ao obter a lista de perfis. Por favor, tente novamente.");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchProfiles();
  }, []);

  const handleDeleteProfile = async (id) => {
    try {
      await profileService.destroy(id);
      // Atualizar a lista de perfis após a exclusão
      fetchProfiles();
      setShowSuccessAlert(true); // Exibir o alerta de sucesso após a exclusão
      setTimeout(() => setShowSuccessAlert(false), 5000); // Ocultar o alerta após 5 segundos
    } catch (error) {
      console.error("Erro ao excluir o perfil:", error);
      // Tratar o erro de exclusão do perfil
      setShowErrorAlert(true); // Exibir o alerta de erro após a falha na exclusão
      setTimeout(() => setShowErrorAlert(false), 5000); // Ocultar o alerta após 5 segundos
    }
  };

  const handleCloseModal = () => {
    setShowConfirmModal(false);
    setSelectedProfileId(null);
  };

  const handleShowConfirmModal = (id) => {
    setSelectedProfileId(id);
    setShowConfirmModal(true);
  };

  return (
    <>
      <NavlogComponent />
      <Container>
        <Row className="justify-content-md-center">
          <Col md={12} className="mt-5">
            <Card className="mt-5">
              <Card.Body>
                <Card.Title>Lista de Perfis</Card.Title>
                <Table striped bordered hover className="table-dark text-white rounded">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Nome do Perfil</th>
                      <th>Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                    {profiles.map((profile, index) => (
                      <tr key={profile.id}>
                        <td>{index + 1}</td>
                        <td>{profile.name}</td>
                        <td>
                          <Button variant="info" size="sm" className="m-1">
                            <Link
                              to={`/profile/update/${profile.id}`}
                              style={{ textDecoration: "none", color: "white" }}
                            >
                              Editar
                            </Link>
                          </Button>

                          <Button variant="danger" size="sm" className="m-1" onClick={() => handleShowConfirmModal(profile.id)}>
                            Excluir
                          </Button>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </Table>
                {loading && <p>Carregando...</p>}
                {error && <Alert variant="danger">{error}</Alert>} {/* Exibir alerta de erro se houver um erro */}
              </Card.Body>
            </Card>
          </Col>
        </Row>
        <Link to="/profile/create">
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

      {/* Modal de confirmação para exclusão */}
      <Modal show={showConfirmModal} onHide={handleCloseModal}>
        <Modal.Header closeButton>
          <Modal.Title>Confirmar Exclusão</Modal.Title>
        </Modal.Header>
        <Modal.Body>Deseja realmente excluir este perfil?</Modal.Body>
        <Modal.Footer>
          <Button variant="secondary" onClick={handleCloseModal}>
            Cancelar
          </Button>
          <Button variant="danger" onClick={() => { handleDeleteProfile(selectedProfileId); handleCloseModal(); }}>
            Excluir
          </Button>
        </Modal.Footer>
      </Modal>

      {/* Alerta de sucesso após a exclusão */}
      <Alert variant="success" show={showSuccessAlert} onClose={() => setShowSuccessAlert(false)} dismissible style={{ position: "fixed", top: "10px", right: "10px", zIndex: "1050" }}>
        Perfil excluído com sucesso.
      </Alert>

      {/* Alerta de erro após falha na exclusão */}
      <Alert variant="danger" show={showErrorAlert} onClose={() => setShowErrorAlert(false)} dismissible style={{ position: "fixed", top: "10px", right: "10px", zIndex: "1050" }}>
        Erro ao excluir o perfil. Por favor, tente novamente.
      </Alert>
    </>
  );
};

export default ProfileListPage;

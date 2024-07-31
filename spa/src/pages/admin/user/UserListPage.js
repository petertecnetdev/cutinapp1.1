import React, { useState, useEffect } from "react";
import {
  Button,
  Card,
  Col,
  Container,
  Row,
  Table,
  Alert,
} from "react-bootstrap";
import { Link } from "react-router-dom";
import userService from "../../../services/UserService";
import NavlogComponent from "../../../components/NavlogComponent";
import { storageUrl } from "../../../config";

const UserListPage = () => {
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const fetchUsers = async () => {
    setLoading(true);
    setError(null);
    try {
      const response = await userService.list();
      setUsers(response.users || []);
    } catch (error) {
      console.error("Erro ao obter a lista de usuários:", error);
      setError(
        "Erro ao obter a lista de usuários. Por favor, tente novamente."
      );
    } finally {
      setLoading(false);
    }
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
            <Card className="mt-5">
              <Card.Body>
                <Card.Title>Lista de Usuários</Card.Title>
                <Table
                  striped
                  bordered
                  hover
                  className="table-dark text-white rounded"
                >
                  <thead>
                    <tr>
                      <th>Avatar</th>
                      <th>Nome do Usuário</th>
                      <th>E-mail</th>
                      <th>User Name</th>
                    </tr>
                  </thead>
                  <tbody>
                    {users.map((user) => (
                      <tr key={user.id}>
                        <td className="text-center">
                          {" "}
                          <img
                            src={
                              user.avatar
                                ? `${storageUrl}/${user.avatar}`
                                : "/images/loadingimage.gif"
                            }
                            alt="User Avatar"
                            className="avatar "
                            style={{ maxWidth: "50px", borderRadius: "50%" }}
                          />
                        </td>
                        <td>
                          {user.first_name} {user.last_name}
                        </td>
                        <td>{user.email}</td>
                        <td>
                          {" "}
                          <Link
                            to={`/user/${user.user_name}`}
                            style={{ textDecoration: "none" }}
                          >
                            {user.user_name}
                          </Link>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </Table>
                {loading && <p>Carregando...</p>}
                {error && <Alert variant="danger">{error}</Alert>}
              </Card.Body>
            </Card>
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
    </>
  );
};

export default UserListPage;

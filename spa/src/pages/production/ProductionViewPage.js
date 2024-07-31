import React, { useState, useEffect } from "react";
import { useParams } from "react-router-dom";
import { Container, Row, Col, Card, Button, Modal } from "react-bootstrap";
import NavlogComponent from "../../components/NavlogComponent";
import productionService from "../../services/ProductionService";
import { storageUrl } from "../../config";
import { Link } from "react-router-dom";
import LoadingComponent from "../../components/LoadingComponent";

const ProductionViewPage = () => {
  const { slug } = useParams();
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [showAllSegments, setShowAllSegments] = useState({});

  // Função para alternar entre mostrar todos os seguimentos e mostrar apenas os primeiros 3
  const toggleSegments = (productionId) => {
    setShowAllSegments({
      ...showAllSegments,
      [productionId]: !showAllSegments[productionId],
    });
  };

  useEffect(() => {
    const fetchProduction = async () => {
      try {
        const response = await productionService.view(slug);
        setData(response.data);
        setLoading(false);
      } catch (error) {
        console.error("Error fetching production:", error);
        setLoading(false);
      }
    };

    fetchProduction();
  }, [slug]);

  if (loading) {
    return <LoadingComponent />;
  }

  return (
    <>
      <NavlogComponent />
      {data.production.logo && (
        <img
          src={`${storageUrl}/${data.production.background}`}
          alt="Preview da Logo"
          className="img-fluid img-background-production"
        />
      )}
      {data.production.logo && (
        <img
          src={`${storageUrl}/${data.production.logo}`}
          alt="Preview da Logo"
          className="img-fluid rounded-circle img-logo-production"
        />
      )}
      <Container>
        <Row>
          
        <Col md={8} className="mx-auto">
          <p className="labeltitle h4 text-center text-uppercase">
            {data.production.name}
          </p>
          </Col></Row>
          <Row>
          <Col md={4}>
            <Card>
              <Card.Text className="text-center text-uppercase">
                {data.production.establishment_type} |
                <strong>
                  {data.views} <i className="fa fa-eye" aria-hidden="true"></i>
                </strong>
                |
                <strong>
                  {data.production.city} {data.production.uf}
                </strong>
              </Card.Text>
              <Card.Text className="text-center">
                <a
                  href={data.production.location}
                  target="_blank"
                  rel="noopener noreferrer"
                  style={{ cursor: "pointer" }}
                >
                  <i className="bi bi-geo-alt btn btn-outline-light"></i>
                </a>
                <a
                  href={data.production.instagram_url}
                  target="_blank"
                  rel="noopener noreferrer"
                  style={{ cursor: "pointer" }}
                >
                  <i className="bi bi-instagram m-2 btn btn-outline-light"></i>
                </a>
              </Card.Text>

              <Card.Text>
                <i className="bi bi-map m-2"></i>
                {data.production.address}
              </Card.Text>
              <Card.Text>{data.production.youtube_url}</Card.Text>
            </Card>
          </Col>
          <Col md={8}>
            <Card>
              <div className="d-flex flex-wrap justify-content-center">
                {data.production.segments.slice(0, 3).map((segment, index) => (
                  <p
                    key={index}
                    className="seguiments text-center text-uppercase "
                  >
                    {segment}
                  </p>
                ))}
                {data.production.segments.length > 3 && (
                  <Button
                    variant="link"
                    onClick={() => toggleSegments(data.production.id)}
                    style={{ textDecoration: "none" }} // Removendo o sublinhado
                  >
                    <p className="seguiments text-center"> +</p>
                  </Button>
                )}
              </div>
              {/* Modal para mostrar todos os seguimentos */}
              <Modal
                show={showAllSegments[data.production.id]}
                onHide={() => toggleSegments(data.production.id)}
              >
                <Modal.Header closeButton>
                  <Modal.Title>
                    Seguimentos de {data.production.name}{" "}
                  </Modal.Title>
                </Modal.Header>
                <Modal.Body>
                  {data.production.segments.map((segment, index) => (
                    <p key={index} className="seguiments text-center">
                      {segment}
                    </p>
                  ))}
                </Modal.Body>
              </Modal>
              <Card.Text className="text-center">
                {data.production.description}
              </Card.Text>
              <p className="text-info">Fudador(a):</p>
              <Link
                to={`/user/${data.production.user.user_name}`}
                style={{ textDecoration: "none" }}
              >
                <div className="rounded-circle m-2">
                  {data.production.user.avatar && (
                    <img
                      src={`${storageUrl}/${data.production.user.avatar}`}
                      alt={`${data.production.user.first_name} Produtor da produção ${data.production.name} da Logo`}
                      className="rounded-circle"
                      style={{ width: "50px", height: "50px" }}
                    />
                  )}
                </div>

                <Card.Text>
                  <i className="bi bi-file-earmark-person  m-2 "></i>
                  {data.production.user.first_name}
                </Card.Text>
              </Link>
            </Card>
          </Col>
        </Row>
        <Row>
  <p className="labeltitle h4 text-center text-uppercase">Agenda</p>
  {data.productionEvents.length > 0 && (
    data.productionEvents.map((event) => (
      <Col key={event.id} md={3} className="m-1">
          <Link
                      to={`/event/${event.slug}`}
                      style={{ textDecoration: "none" }}
                    >
                  
                         <img
            key={event.id}
            src={`${storageUrl}/${event.image}`}
            alt="Preview da Logo"
            className="img-fluid"
           />
          <p className="labeltitle h6 text-center text-uppercase">{event.title}</p>  
          
          </Link>
      </Col>
    ))
  )}
</Row>


        <Row>
          <p className="labeltitle h1 text-center text-uppercase">
            Outras Produções
          </p>
          {data.productions.map(
            (production) =>
              // Verifica se o ID da produção atual é diferente do ID da produção que estamos visualizando
              // Se forem diferentes, renderiza o componente Col com os detalhes da produção
              data.production.id !== production.id && (
                <Col key={production.id} md={4}>
                  <Card className="card-production">
                    <div
                      className="background-image"
                      style={{
                        backgroundImage: `url('${storageUrl}/${production.background}')`,
                      }}
                    />
                   <img
      src={`${storageUrl}/${production.logo}`}
      className="rounded-circle img-logo-production"
      style={{ margin: '0 auto', display: 'block' }}
    />
                    <Card.Body>
                      {" "}
                      <Link
                        to={`/production/${production.slug}`}
                        style={{ textDecoration: "none" }}
                      >
                        <Card.Title className="text-uppercase text-center labeltitle">
                          {production.name}
                        </Card.Title>
                      </Link>

                      <div className="d-flex flex-wrap justify-content-center seguiment-card" >
                        {production.segments
                          .slice(0, 3)
                          .map((segment, index) => (
                            <p
                              key={index}
                              className="seguiments text-center text-uppercase "
                            >
                              {segment}
                            </p>
                          ))}
                        {production.segments.length > 3 && (
                          <Button
                            variant="link"
                            onClick={() => toggleSegments(production.id)}
                            style={{ textDecoration: "none" }} // Removendo o sublinhado
                          >
                            <p className="seguiments text-center"> +</p>
                          </Button>
                        )}
                      </div>
                      {/* Modal para mostrar todos os seguimentos */}
                      <Modal
                        show={showAllSegments[production.id]}
                        onHide={() => toggleSegments(production.id)}
                      >
                        <Modal.Header closeButton>
                          <Modal.Title>
                            Seguimentos de {production.name}{" "}
                          </Modal.Title>
                        </Modal.Header>
                        <Modal.Body>
                          {production.segments.map((segment, index) => (
                            <p key={index} className="seguiments text-center">
                              {segment}
                            </p>
                          ))}
                        </Modal.Body>
                      </Modal>
                    </Card.Body>
                  </Card>
                </Col>
              )
          )}
        </Row>
      </Container>
    </>
  );
};

export default ProductionViewPage;

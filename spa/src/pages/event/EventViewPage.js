import React, { useState, useEffect } from "react";
import { Container, Row, Col, Card, Button, Alert } from "react-bootstrap";
import NavlogComponent from "../../components/NavlogComponent";
import { useParams, Link } from "react-router-dom";
import eventService from "../../services/EventService";
import LoadingComponent from "../../components/LoadingComponent";
import { storageUrl } from "../../config";

const EventViewPage = () => {
  const { slug } = useParams();
  const [eventData, setEventData] = useState({});
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchEvent = async () => {
      try {
        const response = await eventService.view(slug);
        setEventData(response.data);
        setLoading(false);
      } catch (error) {
        console.error("Error fetching event:", error);
        setError(
          "Erro ao carregar o evento. Por favor, tente novamente mais tarde."
        );
        setLoading(false);
      }
    };

    fetchEvent();
  }, [slug]);

  // Função para formatar a data
  const formatDate = (dateString) => {
    const date = new Date(dateString);
    const options = {
      weekday: "long",
      day: "numeric",
      month: "short",
    };
    return date.toLocaleDateString("pt-BR", options);
  };

  // Função para formatar o horário
  const formatTime = (timeString) => {
    const time = new Date(timeString);
    const options = {
      hour: "numeric",
      minute: "numeric",
    };
    return time.toLocaleTimeString("pt-BR", options);
  };

  // Função para verificar se a data de término é no dia seguinte
  const isNextDay = (startDate, endDate) => {
    const start = new Date(startDate);
    const end = new Date(endDate);
    return start.getDate() !== end.getDate();
  };

  if (loading) {
    return <LoadingComponent />;
  }

  return (
    <>
      <NavlogComponent />
      <div
        className="background-image-event"
        style={{
          backgroundImage: `url(${
            eventData.event.image
              ? `${storageUrl}/${eventData.event.image}`
              : "/images/eventflyer.png"
          })`,
        }}
      />
      <Container>
        <Row>
          <p className="labeltitle h5 text-center text-uppercase">
            {eventData.event.title}
          </p>
          <Col md={12} className="">
            <Card className="card-event-view">
              <Card.Img
                variant="top"
                src={
                  eventData.event.image
                    ? `${storageUrl}/${eventData.event.image}`
                    : "/images/eventflyer.png"
                }
                alt={`Evento ${eventData.event.title}`}
                className="rounded"
              />
              <Card.Body>
                <span className="text-warning ">
                  <p className="labeltitle h6 text-warning text-center text-uppercase">
                    {formatDate(eventData.event.start_date)} ás{" "}
                    <strong>{formatTime(eventData.event.start_date)}</strong>{" "}
                    até{" "}
                    {isNextDay(
                      eventData.event.start_date,
                      eventData.event.end_date
                    )
                      ? `às ${formatTime(eventData.event.end_date)} `
                      : `às <strong>${formatTime(
                          eventData.event.end_date
                        )}</strong>`}
                  </p>
                </span>
                <p className="labeltitle h6 text-warning text-center text-danger">
                    {" "}
                    <a
                      href={eventData.event.location}
                      target="_blank"
                      rel="noopener noreferrer"
                      style={{ cursor: "pointer", textDecoration: "none" }}
                    >
                      <i className="bi h4 bi-geo-alt btn text-white m-2"></i>
                      {eventData.event.establishment_name}
                    </a>
                    {" "} {eventData.event.address},{eventData.event.city} -{" "}
                  {eventData.event.uf}
                  {eventData.event.cep}
                  
                  </p>
              </Card.Body>
            </Card>
            <div
              className="background-image-event"
              style={{
                backgroundImage: `url(${
                  eventData.event.image
                    ? `${storageUrl}/${eventData.event.image}`
                    : "/images/eventflyer.png"
                })`,
              }}
            />
            <Card>
              <Card.Body>
                <Card.Text className="text-start">{eventData.event.description}</Card.Text>
              </Card.Body>
            </Card>
          </Col>

        </Row>
        <div
          className="background-image-event"
          style={{
            backgroundImage: `url(${
              eventData.event.image
                ? `${storageUrl}/${eventData.event.image}`
                : "/images/eventflyer.png"
            })`,
          }}
        />
        <Row>
          <p className="labeltitle h-5 text-center text-uppercase">
            Outros eventos
          </p>
          {eventData.events.map((otherevent) => {
            if (eventData.event.id !== otherevent.id) {
              return (
                <Col key={otherevent.id} md={4}>
                  <Card className="card-otherevent">
                    {otherevent.image && (
                      <Card.Img
                        variant="top"
                        src={`${storageUrl}/${otherevent.image}`}
                        className="img-otherevent"
                      />
                    )}
                    <Link
                      to={`/event/${otherevent.slug}`}
                      style={{ textDecoration: "none" }}
                    >
                      <p className="labeltitle h-5 text-center text-uppercase">
                        {otherevent.title}
                      </p>
                    </Link>
                    <Card.Text className="text-center">
                      <div className="d-flex flex-wrap justify-content-center">
                        {otherevent.segments
                          .slice(0, 3)
                          .map((segment, index) => (
                            <p
                              key={index}
                              className="seguiments text-center h5 text-uppercase"
                            >
                              {segment}
                            </p>
                          ))}
                        {otherevent.segments.length > 3 && (
                          <Button
                            variant="link"
                            style={{ textDecoration: "none" }}
                          >
                            <p className="seguiments text-center">+</p>
                          </Button>
                        )}
                      </div>
                      {/* Modal para mostrar todos os seguimentos */}
                    </Card.Text>
                  </Card>
                </Col>
              );
            }
            return null;
          })}
        </Row>
      </Container>
      {error && (
        <Alert
          variant="danger"
          onClose={() => setError(null)}
          dismissible
          style={{
            position: "fixed",
            top: "150px",
            right: "10px",
            zIndex: "1050",
          }}
        >
          {error}
        </Alert>
      )}
      <Link to={`/event/corp/list`}>
        <Button
          variant="secondary"
          style={{
            position: "fixed",
            bottom: "20px",
            right: "20px",
            zIndex: "1050",
          }}
        >
          Voltar
        </Button>
      </Link>
    </>
  );
};

export default EventViewPage;

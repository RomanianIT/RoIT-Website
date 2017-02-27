<?php

namespace WebsiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use WebsiteBundle\Entity\Contact;
use WebsiteBundle\Entity\Event;
use WebsiteBundle\Entity\Member;
use WebsiteBundle\Entity\Ambasador;
use WebsiteBundle\Entity\Project;
use ReCaptcha\ReCaptcha;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('WebsiteBundle:Default:index.html.twig');
    }

    public function zoneAction($name, $id)
    {

        $zone = $this->getDoctrine()->getManager()->getRepository('WebsiteBundle:Zone')->findOneBy(['id'=>$id]);
        return $this->render('WebsiteBundle:Default:zone.html.twig', array('zone' => $zone));
    }

    public function centerMembersAction($id)
    {
        $center = $this->getDoctrine()->getManager()->getRepository('WebsiteBundle:Center')->findOneBy(['id'=>$id]);

        return $this->render('WebsiteBundle:Default:centerMembers.html.twig', array('center' => $center));
    }

    public function centerEventsAction($id)
    {
        $center = $this->getDoctrine()->getManager()->getRepository('WebsiteBundle:Center')->findOneBy(['id'=>$id]);

        $futureEvents = [];
        $today = $now    = new \DateTime();

        /** @var Event $event */
        foreach ($center->getEvents() as $event)
        {
            if ($event->getDate() > $today ) {
                $futureEvents[] = $event;
            }
        }

        return $this->render('WebsiteBundle:Default:centerEvents.html.twig', array('center' => $center, 'futureEvents' => $futureEvents));
    }

    public function membersMapAction()
    {
        $members = $this->getDoctrine()->getManager()->getRepository('WebsiteBundle:Member')->findAll();

        return $this->render('WebsiteBundle:Default:map.html.twig', array('members' => $members));
    }

    public function zoneListAction()
    {
        $zones = $this->getDoctrine()->getManager()->getRepository('WebsiteBundle:Zone')->findActiveZones();

        return $this->render('WebsiteBundle:Default:zone_list.html.twig', array('zones' => $zones));
    }

    public function lastEventsAction()
    {
        $events = $this->getDoctrine()->getManager()->getRepository('WebsiteBundle:Event')->findBy(array(), array('date' => 'DESC'), 5);
        return $this->render('WebsiteBundle:Default:lastEvents.html.twig', array('events' => $events));
    }

    public function contactAction() {
        $contactModel = new Contact();
        $form = $this->createForm('WebsiteBundle\Form\ContactType', $contactModel);

        return $this->render('WebsiteBundle:Default:contact.html.twig', ['form' => $form->createView()]);
    }

    public function numbersAction()
    {
        $members = $this->getDoctrine()->getManager()->getRepository('WebsiteBundle:Member')->countAll();
        $centers = $this->getDoctrine()->getManager()->getRepository('WebsiteBundle:Center')->countAll();
        $events = $this->getDoctrine()->getManager()->getRepository('WebsiteBundle:Event')->countAll();
        $projects = $this->getDoctrine()->getManager()->getRepository('WebsiteBundle:Project')->countAll();

        return $this->render('WebsiteBundle:Default:numbers.html.twig', array('members' => $members, 'centers' => $centers, 'events' => $events, 'projects' => $projects));
    }

    public function sendContactAction(Request $request)
    {

        //Get new instance of ReCaptcha
        $recaptcha = new ReCaptcha($this->getParameter('google_recaptca_secret_key'));
        $resp = $recaptcha->verify($request->request->get('g-recaptcha-response'), $request->getClientIp());

        $contactModel = new Contact();
        $contactForm = $this->createForm('WebsiteBundle\Form\ContactType', $contactModel);
        $contactForm->handleRequest($request);

        //Validate the google captcha
        if (!$resp->isSuccess()) {
            $this->get('session')->getFlashBag()->set(
                'error',
                array(
                    'title' => 'EROARE!',
                    'message' => 'Validatea nu este completa. Te rugam sa folosite captcha '
                )
            );

            //Get Back to the same page if error
            return $this->render('@Website/Default/index.html.twig', array(
                'form' => $contactForm->createView()
            ));
        }


        if ($contactForm->isValid()) {
            $message = \Swift_Message::newInstance()
                ->setSubject('Contact form')
                ->setFrom('ro.it.git@gmail.com')
                ->setTo(array($this->container->getParameter('contact.test_email'), 'ro.it.git@gmail.com'))
                ->setBody(
                    $this->renderView(
                        'WebsiteBundle:Mail:contact.html.twig',
                        array(
                            'ip' => $request->getClientIp(),
                            'name' => $contactModel->getName(),
                            'message' => $contactModel->getMessage(),
                            'email' => $contactModel->getEmail(),
                        )
                    ), 'text/html'
                );



            $sent = $this->get('mailer')->send($message);

            if ($sent) {
                $this->get('session')->getFlashBag()->set(
                    'success',
                    array(
                        'title' => 'SUCCESS!',
                        'message' => 'Mesajul a fost trimis cu succes. Multumim.'
                    )
                );
            }
        } else {
            $this->get('session')->getFlashBag()->set(
                'error',
                array(
                    'title' => 'EROARE!',
                    'message' => 'Formularul de contact nu a fost validat.'
                )
            );
        }

        return $this->redirectToRoute('website_homepage');
    }

    public function listEventsAction()
    {
        $em = $this->getDoctrine()->getManager();

        $events = $em->getRepository('WebsiteBundle:Event')->findAll();

        return $this->render('WebsiteBundle:Default:listEvents.html.twig', ['events' => $events]);
    }

    public function listProjectsAction()
    {
        $em = $this->getDoctrine()->getManager();

        $projects = $em->getRepository('WebsiteBundle:Project')->findBy(['isActive' => true]);

        return $this->render('WebsiteBundle:Default:listProjects.html.twig', ['projects' => $projects]);
    }

    public function listPartnersAction()
    {
        $em = $this->getDoctrine()->getManager();

        $partners = $em->getRepository('WebsiteBundle:Partner')->findAll();

        $groupedPartners = [];

        foreach ($partners as $partner) {
            $groupedPartners[$partner->getType()][] = $partner;
        }

        return $this->render('WebsiteBundle:Default:listPartners.html.twig', ['partners' => $groupedPartners]);
    }

    public function registerMemberAction(Request $request)
    {
        $member = new Member();
        $form = $this->createForm('WebsiteBundle\Form\MemberType', $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();

            $checkEmail = $em->getRepository('WebsiteBundle:Member')->findOneBy(array('email' => $member->getEmail()));

            if (!is_null($checkEmail)) {
                $this->get('session')->getFlashBag()->set(
                    'warning',
                    array(
                        'title' => 'ATENTIE!',
                        'message' => 'Există deja un membru înregistrat cu acest email.'
                    )
                );
            } else {

                $em->persist($member);
                $em->flush();

                $this->get('session')->getFlashBag()->set(
                    'success',
                    array(
                        'title' => 'SUCCESS!',
                        'message' => 'Înregistrarea cu succes. Bine ai venit.'
                    )
                );

                //Send email to user
                $message = \Swift_Message::newInstance()
                    ->setSubject('Bine ai venit in Romanian IT')
                    ->setFrom('office@romanianit.com', 'Echipa Romanian IT')
                    ->setTo(array($member->getEmail()))
                    ->setBody(
                        $this->renderView(
                            'WebsiteBundle:Mail:register.html.twig',
                            array()
                        ), 'text/html'
                    );

                $sent = $this->get('mailer')->send($message);
                $this->get('slack')->inviteUser($member);

                return $this->redirectToRoute('website_share', array());
            }
        }
        return $this->render('WebsiteBundle:Default:registerMember.html.twig',
            ['member' => $member, 'form' => $form->createView()]
        );
    }

    public function registerAmbasadorAction(Request $request)
    {
        $member = new Member();
        $form = $this->createForm('WebsiteBundle\Form\MemberType', $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();

            $checkEmail = $em->getRepository('WebsiteBundle:Member')->findOneBy(array('email' => $member->getEmail()));

            if (!is_null($checkEmail)) {
                $this->get('session')->getFlashBag()->set(
                    'warning',
                    array(
                        'title' => 'ATENTIE!',
                        'message' => 'Există deja un membru înregistrat cu acest email.'
                    )
                );
            } else {

                $em->persist($member);
                $em->flush();

                $this->get('session')->getFlashBag()->set(
                    'success',
                    array(
                        'title' => 'SUCCESS!',
                        'message' => 'Înregistrarea cu succes. Bine ai venit.'
                    )
                );

                return $this->redirectToRoute('website_share', array());
            }
        }
        return $this->render('WebsiteBundle:Default:registerMember.html.twig',
            ['member' => $member, 'is_ambasador' => true, 'form' => $form->createView()]
        );
    }

    public function newProjectAction(Request $request)
    {
        $project = new Project();
        $form = $this->createForm('WebsiteBundle\Form\ProjectType', $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($project);
            $em->flush();

            $this->get('session')->getFlashBag()->set(
                'success',
                array(
                    'title' => 'SUCCESS!',
                    'message' => 'Înregistrarea cu succes. Mulțumim.'
                )
            );

            return $this->redirectToRoute('website_projects_new', array());
        }

        return $this->render('WebsiteBundle:Default:newProject.html.twig',
            ['project' => $project,
            'form' => $form->createView()]
        );
    }

    public function stepAction(Request $request)
    {
        $member = new Member();
        $form = $this->createForm('WebsiteBundle\Form\MemberType', $member);
        $form->handleRequest($request);

        return $this->render('WebsiteBundle:Default:step.html.twig', ['form' => $form->createView()]);
    }

    public function shareAction()
    {
        return $this->render('WebsiteBundle:Default:share.html.twig');
    }

    public function mergeIndexAction()
    {
        return $this->render('WebsiteBundle:Default:merge_index.html.twig');
    }

    public function registerIndexAction(Request $request)
    {
        $pass = $request->request->get('password');
        $indexApiClientId = $this->getParameter('index_client_id');
        $indexApiUrl = $this->getParameter('index_api_url');


        $this->redirect('https://app.index.io/login');
    }

    public function volunteerAction()
    {
        return $this->render('WebsiteBundle:Default:volunteer.html.twig');
    }

    public function donateAction()
    {
        return $this->render('WebsiteBundle:Default:donate.html.twig');
    }

    public function privacyAction()
    {
        return $this->render('WebsiteBundle:Default:privacy.html.twig');
    }

    public function campaignTwoAction()
    {
        return $this->render('WebsiteBundle:Default:campaignTwo.html.twig');
    }

    public function downloadFileAction(Request $request, $filename)
    {
        $response = new Response();
        $filePath = $this->get('kernel')->getRootDir(). '/../web/download/'.$filename;

        if (!file_exists($filePath)) {
            $response->setStatusCode(404, "File not found");

            return $response;
        }

        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', mime_content_type($filePath));
        $response->headers->set('Content-Disposition', 'attachment; filename="' . basename($filePath) . '";');
        $response->headers->set('Content-length', filesize($filePath));

        // Send headers before outputting anything
        $response->sendHeaders();

        $response->setContent(file_get_contents($filePath));

        return $response;
    }
}
